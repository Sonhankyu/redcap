<?php
namespace ExternalModules;

const TEST_FORM = 'test_form';
const TEST_RECORD_ID = 'test_record_id';
const TEST_TEXT_FIELD = 'test_text_field';
const TEST_SQL_FIELD = 'test_sql_field';
const TEST_RADIO_FIELD = 'test_radio_field';
const TEST_CHECKBOX_FIELD = 'test_checkbox_field';
const TEST_YESNO_FIELD = 'test_yesno_field';
const TEST_CALC_FIELD = 'test_calc_field';
const TEST_REPEATING_FORM = 'test_repeating_form';
const TEST_REPEATING_FIELD_1 = 'test_repeating_field_1';
const TEST_REPEATING_FIELD_2 = 'test_repeating_field_2';
const TEST_REPEATING_FIELD_3 = 'test_repeating_field_3';
const TEST_REPEATING_FORM_2 = 'test_repeating_form_2';
const TEST_REPEATING_FORM_2_FIELD_1 = 'test_repeating_form_2_field_1';

// These were added simply to avoid warnings from REDCap code.
$_SERVER['SERVER_NAME'] = 'unit testing';
$_SERVER['REMOTE_ADDR'] = 'unit testing';
if(!defined('PAGE')){
	define('PAGE', 'unit testing');
}

require_once __DIR__ . '/../redcap_connect.php';

// Required by PHP 8
define('ACCESS_CONTROL_CENTER', true);
define('SUPER_USER', true);
define('ACCESS_ADMIN_DASHBOARDS', true);
define('ACCOUNT_MANAGER', true);
define('ADMIN_RIGHTS', true);
define('ACCESS_EXTERNAL_MODULE_INSTALL', true);
define('ACCESS_SYSTEM_CONFIG', true);
define('USERID', null);

use PHPUnit\Framework\TestCase;
use \Exception;
use REDCap;

const TEST_MODULE_PREFIX = ExternalModules::TEST_MODULE_PREFIX;
const TEST_MODULE_TWO_PREFIX = ExternalModules::TEST_MODULE_TWO_PREFIX;
const TEST_MODULE_VERSION = ExternalModules::TEST_MODULE_VERSION;
const TEST_LOG_MESSAGE = 'This is a unit test log message';
const TEST_SETTING_KEY = 'unit-test-setting-key';
const FILE_SETTING_KEY = 'unit-test-file-setting-key';

require_once ExternalModules::getTestModuleDirectoryPath(TEST_MODULE_PREFIX) . '/TestModule.php';
require_once ExternalModules::getTestModuleDirectoryPath(TEST_MODULE_TWO_PREFIX) . '/TestModuleTwo.php';

$testPIDs = ExternalModules::getTestPIDs();
define('TEST_SETTING_PID', $testPIDs[0]);
define('TEST_SETTING_PID_2', $testPIDs[1]);

abstract class BaseTest extends TestCase
{
	protected $backupGlobals = FALSE;

	private static $originalServerArray;
	private static $testModuleInstance;
	private static $testProjectsInitialized;

	public static function setUpBeforeClass():void{
		ExternalModules::initialize();
	}

	function getEventIds($projectId){	
		$sql = '	
			select event_id	
			from redcap_events_arms a	
			join redcap_events_metadata m	
				on m.arm_id = a.arm_id	
			where project_id = ?	
		';	

		$result = self::query($sql, $projectId);	

		$eventIds = [];
		while($row = $result->fetch_assoc()){
			$eventIds[] = $row['event_id'];
		}
		
		return $eventIds;
    }

	protected function setUp():void{
		self::$testModuleInstance = new TestModule(TEST_MODULE_PREFIX);

		new TestModuleTwo(TEST_MODULE_TWO_PREFIX); // This line caches the framework instance for prefix two.
		
		$this->setExternalModulesProperty('systemwideEnabledVersions', [
			TEST_MODULE_PREFIX => TEST_MODULE_VERSION,
			TEST_MODULE_TWO_PREFIX => TEST_MODULE_VERSION
		]);

		// Simulate "Enable module on all projects by default"
		$this->setExternalModulesProperty('projectEnabledDefaults', [
			TEST_MODULE_PREFIX => true,
			TEST_MODULE_TWO_PREFIX => true,
		]);
		
		$this->cleanupSettings();

		if(!self::$testProjectsInitialized){
			foreach([TEST_SETTING_PID, TEST_SETTING_PID_2] as $pid){
				$framework = $this->getFramework();

				// Fixes some inconsistencies that occasionally crash importDataDictionary()
				ExternalModules::query('
					delete from redcap_events_forms where event_id in (
						select event_id
						from redcap_events_arms a
						join redcap_events_metadata em
							on a.arm_id = em.arm_id
						where project_id = ?
					)
				', $pid);

				if($pid === TEST_SETTING_PID_2){
					$this->setupSecondEvent(TEST_SETTING_PID_2);
				}

				$framework->importDataDictionary($pid, __DIR__ . '/test-project-data-dictionary.csv');
				
				foreach([TEST_REPEATING_FORM, TEST_REPEATING_FORM_2] as $form){
					$eventIds = $this->getEventIds($pid);
					foreach($eventIds as $eventId){
						ExternalModules::query('delete from redcap_events_repeat where event_id = ? and form_name = ?', [$eventId, $form]);
						ExternalModules::query('insert into redcap_events_repeat values (?, ?, null)', [$eventId, $form]);
					}
				}

				list($surveyId, $formName) = $framework->getSurveyId(TEST_SETTING_PID);
				if(empty($surveyId)){
					ExternalModules::query("
						insert into redcap_surveys (project_id, form_name)
						values (?, (
							select form_name from redcap_metadata where project_id = ? limit 1
						))	
					", [$pid, $pid]);
				}
			}

			self::$testProjectsInitialized = true;
		}

		// We must clear the project cache so our updates are pulled from the DB.
		$this->setPrivateVariable('project_cache', [], 'Project');

		// This will only remove logs for this module.
		$this->getInstance()->removeLogs('1 = 1', []);

		// Clear the data between tests
		foreach(ExternalModules::getTestPIDs() as $pid){
			self::query('delete from redcap_data where project_id = ?', $pid);
			self::query('delete from redcap_record_list where project_id = ?', $pid);
			self::query("update redcap_record_counts set record_count = 0, time_of_count = ? where project_id = ?", [NOW, $pid]);
		}
	}

	private function setupSecondEvent($pid){
		ExternalModules::query('update redcap_projects set repeatforms = 1 where project_id = ?', $pid);

		$project = new \Project($pid);
		
		$existingEventNames = [];
		foreach($project->events as $arm){
			foreach($arm['events'] as $eventId=>$event){
				$existingEventNames[$event['descrip']] = true;
			}
		}
		
		$desiredEvents = [
			'Event 1' => 'event_1_arm_1',
			'Event 2' => 'event_2_arm_1',
		];

		foreach($desiredEvents as $eventName=>$uniqueEventName){
			if(!isset($existingEventNames[$eventName])){
				\Event::create($pid, [
					'arm_num' => 1,
					'day_offset' => 0,
					'offset_min' => 0,
					'offset_max' => 0,
					'event_name' => $eventName
				]);
			}

			foreach([TEST_FORM, TEST_REPEATING_FORM, TEST_REPEATING_FORM_2] as $form){
				$project->addEventForms([[
					'form' => $form,
					'unique_event_name' => $uniqueEventName
				]]);
			}
		}
	}

	function getFrameworkVersion(){
		return 3;
	}

	protected function tearDown():void
	{
		self::cleanupSettings();
		$this->setActiveModulePrefix(null);
	}

	private function cleanupSettings()
	{
		foreach([TEST_MODULE_PREFIX, TEST_MODULE_TWO_PREFIX] as $prefix){
			if($prefix === TEST_MODULE_PREFIX){
				$permissions = ['redcap_test_call_function']; // Give permissions to a hook used by multiple tests.
			}
			else{
				$permissions = [];
			}

			$config = [
				'framework-version' => $this->getFrameworkVersion(),
				'permissions' => $permissions
			];

			$this->setConfig($config, true, $prefix);
	
			$m = ExternalModules::getModuleInstance($prefix, TEST_MODULE_VERSION);
			$m->testHookArguments = null;
			
			$moduleId = ExternalModules::getIdForPrefix($prefix);
			$lockName = ExternalModules::getLockName($moduleId, TEST_SETTING_PID);
	
			$m->query("SELECT GET_LOCK(?, 5)", [$lockName]);
			$m->query("delete from redcap_external_module_settings where external_module_id = ?", [$moduleId]);
			$m->query("SELECT RELEASE_LOCK(?)", [$lockName]);
		}

		$_GET = [];
		$_POST = [];
		$_SERVER = self::getOriginalServerArray();

		ExternalModules::setSuperUser(true);
		ExternalModules::setUsername(null);
	}

	private function getOriginalServerArray()
	{
		if(!isset(self::$originalServerArray)){
			self::$originalServerArray = $_SERVER;
		}

		return self::$originalServerArray;
	}

	protected function setSystemSetting($value)
	{
		self::getInstance()->setSystemSetting(TEST_SETTING_KEY, $value);
	}

	protected function getSystemSetting()
	{
		return self::getInstance()->getSystemSetting(TEST_SETTING_KEY);
	}

	protected function removeSystemSetting()
	{
		self::getInstance()->removeSystemSetting(TEST_SETTING_KEY);
	}

	protected function setProjectSetting($value)
	{
		self::getInstance()->setProjectSetting(TEST_SETTING_KEY, $value, TEST_SETTING_PID);
	}

	protected function getProjectSetting()
	{
		return self::getInstance()->getProjectSetting(TEST_SETTING_KEY, TEST_SETTING_PID);
	}

	protected function removeProjectSetting()
	{
		self::getInstance()->removeProjectSetting(TEST_SETTING_KEY, TEST_SETTING_PID);
	}

	protected function getInstance()
	{
		return self::$testModuleInstance;
	}

	protected function setConfig($config, $setFrameworkVersionIfMissing = true, $prefix = TEST_MODULE_PREFIX)
	{
		if(gettype($config) === 'string'){
			$config = json_decode($config, true);
			if($config === null){
				throw new Exception("Error parsing json configuration (it's likely not valid json).");
			}
		}

		ExternalModules::setMissingConfigSections($config);

		if($setFrameworkVersionIfMissing && !isset($config['framework-version'])){
			$config['framework-version'] = $this->getFrameworkVersion();
		}

		ExternalModules::setCachedConfig($prefix, TEST_MODULE_VERSION, false, $config);
		ExternalModules::setCachedConfig($prefix, TEST_MODULE_VERSION, true, ExternalModules::translateConfig($config, $prefix));

		// Re-initialize the framework in case the version changed.
		$frameworkInstance = ExternalModules::getFrameworkInstance($prefix, TEST_MODULE_VERSION);
		$this->callPrivateMethodForClass($frameworkInstance, 'initialize');
	}

	private function setExternalModulesProperty($name, $value)
	{
		$externalModulesClass = new \ReflectionClass("ExternalModules\\ExternalModules");
		$configsProperty = $externalModulesClass->getProperty($name);
		$configsProperty->setAccessible(true);
		$configsProperty->setValue($value);
	}

	protected function assertThrowsException($callable, $exceptionExcerpt)
	{
		$exceptionThrown = false;
		try{
			$callable();
		}
		catch(Exception $e){
			if(empty($exceptionExcerpt)){
				throw new Exception('You must specify an exception excerpt!  Here\'s a hint: ' . $e->getMessage());
			}

			$exceptionExcerpt = "*$exceptionExcerpt*";
			for($i=0; $i<10; $i++){
				// Use a wildcard to ignore parameters in tt() strings.
				$exceptionExcerpt = str_replace('{' . $i . '}', '*', $exceptionExcerpt);
			}

			if(!fnmatch($exceptionExcerpt, $e->getMessage())){
				throw new Exception("Could not find the string '$exceptionExcerpt' in the following exception message: " . $e->getMessage() . "\n\n" . $e->getTraceAsString());
			}

			$exceptionThrown = true;
		}

		$this->assertTrue($exceptionThrown, "An exception was not thrown where one was expected containing the following text: $exceptionExcerpt");
	}

	protected function callPrivateMethod($methodName)
	{
		$args = func_get_args();
		array_unshift($args, $this->getReflectionClass());

		return call_user_func_array([$this, 'callPrivateMethodForClass'], $args);
	}

	protected function callPrivateMethodForClass()
	{
		$args = func_get_args();
		$classInstanceOrName = array_shift($args); // remove the $classInstanceOrName
		$methodName = array_shift($args); // remove the $methodName

		if(gettype($classInstanceOrName) == 'string'){
			$instance = null;
		}
		else{
			$instance = $classInstanceOrName;
		}

		$class = new \ReflectionClass($classInstanceOrName);
		$method = $class->getMethod($methodName);
		$method->setAccessible(true);

		return $method->invokeArgs($instance, $args);
	}

	protected function getPrivateVariable($name)
	{
		$class = new \ReflectionClass($this->getReflectionClass());
		$property = $class->getProperty($name);
		$property->setAccessible(true);

		return $property->getValue($this->getReflectionClass());
	}

	protected function setPrivateVariable($name, $value, $target = null)
	{
		if(!$target){
			$target = $this->getReflectionClass();
		}
		
		$class = new \ReflectionClass($target);
		$property = $class->getProperty($name);
		$property->setAccessible(true);

		return $property->setValue($this->getReflectionClass(), $value);
	}

	protected function getReflectionClass()
	{
		return $this->getInstance();
    }

	protected function runConcurrentTestProcesses($functionName, $parentAction, $childAction)
	{
		// The parenthesis are included in the argument and check below so we can still filter for this function manually (WITHOUT the parenthesis)  when testing for testing and avoid triggering the recursion.
		$functionName .= '()';

		global $argv;
		if(end($argv) === $functionName){
			// This is the child process.
			$childAction();
		}
		else{
			// This is the parent process.

			$cmd = "php " . ExternalModules::getPHPUnitPath() . " --filter " . escapeshellarg($functionName);
			$childProcess = proc_open(
				$cmd, [
					0 => ['pipe', 'r'],
					1 => ['pipe', 'w'],
					2 => ['pipe', 'w'],
				],
				$pipes
			);

			// Gets the child status, but caches the final result since calling proc_get_status() multiple times
			// after a process ends will incorrectly return -1 for the exit code.
			$getChildStatus = function() use ($childProcess, &$lastStatus){
				if(!$lastStatus || $lastStatus['running']){
					$lastStatus = proc_get_status($childProcess);
				}

				return $lastStatus;
			};

			$isChildRunning = function() use ($getChildStatus){
				$status = $getChildStatus();
				return $status['running'];
			};

			$parentAction($isChildRunning);

			while($isChildRunning()){
				// The parent finished before the child.
				// Wait for the child to finish before continuing so that the exit code can be checked below.
				sleep(.1);
			}

			$status = $getChildStatus();
			$exitCode = $status['exitcode'];
			if($exitCode !== 0){
				$output = stream_get_contents($pipes[1]);
				throw new Exception("The child phpunit process for the $functionName test failed with exit code $exitCode and the following output: $output");
			}
		}
	}

	function ensureRecordExists($recordId, $pid = TEST_SETTING_PID){
		REDCap::saveData($pid, 'json', json_encode([[
			$this->getFramework()->getRecordIdField($pid) => $recordId,
		]]));
	}

	function getFramework(){
		return ExternalModules::getFrameworkInstance($this->getInstance()->PREFIX);
	}

	function __call($methodName, $args){
		$callable = [$this->getReflectionClass(), $methodName];
		if(!is_callable($callable)){
			throw new Exception("Not callable: " . $this->getReflectionClass() . '::' . $methodName);
		}

		return call_user_func_array($callable, $args);
	}

	function getActiveModulePrefix(){
		// Call this on the ExternalModules class no matter what test it is called from.
		return $this->callPrivateMethodForClass('ExternalModules\ExternalModules', 'getActiveModulePrefix');
	}

	function setActiveModulePrefix($prefix){
		// Call this on the ExternalModules class no matter what test it is called from.
		return $this->callPrivateMethodForClass('ExternalModules\ExternalModules', 'setActiveModulePrefix', $prefix);
	}

	function saveData($data, $pid = TEST_SETTING_PID){
		if(!is_array($data)){
			throw new Exception("An array of data must be specified.");
		}

		$result = \REDCap::saveData($pid, 'json', json_encode($data));
		if(!empty($result['errors']) || !empty($result['warnings'])){
			throw new Exception('Error saving data: ' . json_encode($result, JSON_PRETTY_PRINT));
		}
	}
	
	function getRandomUsernames($limit = 10)
	{
		$result = ExternalModules::query('
			select username
			from redcap_user_information
			where user_suspended_time is null
			order by rand()
			limit ?
		', [$limit]);
		
		$usernames = [];
		while($row = $result->fetch_assoc()){
			$usernames[] = $row['username'];
		}

		return $usernames;
	}

	function getRandomUsername()
	{
		return $this->getRandomUsernames(1)[0];
	}

	function spoofURL($url){
		$parts = explode('://', $url);
		$parts = explode('/', $parts[1]);
		
		$_SERVER['HTTP_HOST'] = array_shift($parts);

		$selfBase = '/' . implode('/', $parts);

		$_SERVER['PHP_SELF'] = $selfBase;
	}
	
	function spoofTranslation($prefix, $key, $value)
	{
		global $lang;

		if(!empty($prefix)){
			$key = ExternalModules::constructLanguageKey($prefix, $key);
		}

		return $lang[$key] = $value;
	}
}
