<?php
namespace ExternalModules;
require_once 'BaseTest.php';

use \Exception;
use \REDCap;
use \ReflectionClass;
use \ReflectionMethod;

class AbstractExternalModuleTest extends BaseTest
{
	protected function setUp():void
	{
		parent::setUp();

		$m = self::getInstance();

		// To delete all logs, we use a fake parameter to create a where clause that applies to all rows
		// (since removeLogs() requires a where clause).
		$m->removeLogs("some_fake_parameter is null");
	}
	
	function testSettingKeyPrefixes()
	{
		$normalValue = 1;
		$prefixedValue = 2;

		$this->setSystemSetting($normalValue);
		$this->setProjectSetting($normalValue);

		$m = $this->getInstance();
		$m->setSettingKeyPrefix('test-setting-prefix-');
		$this->assertNull($this->getSystemSetting());
		$this->assertNull($this->getProjectSetting());

		$this->setSystemSetting($prefixedValue);
		$this->setProjectSetting($prefixedValue);
		$this->assertSame($prefixedValue, $this->getSystemSetting());
		$this->assertSame($prefixedValue, $this->getProjectSetting());

		$this->removeSystemSetting();
		$this->removeProjectSetting();
		$this->assertNull($this->getSystemSetting());
		$this->assertNull($this->getProjectSetting());

		$m->setSettingKeyPrefix(null);
		$this->assertSame($normalValue, $this->getSystemSetting());
		$this->assertSame($normalValue, $this->getProjectSetting());

		// Prefixes with sub-settings are tested in testSubSettings().
	}

	function testSystemSettings()
	{
		$value = rand();
		$this->setSystemSetting($value);
		$this->assertSame($value, $this->getSystemSetting());

		$this->removeSystemSetting();
		$this->assertNull($this->getSystemSetting());
	}

	function testProjectSettings()
	{
		$projectValue = rand();
		$systemValue = rand();

		$this->setProjectSetting($projectValue);
		$this->assertSame($projectValue, $this->getProjectSetting());

		$this->removeProjectSetting();
		$this->assertNull($this->getProjectSetting());

		$this->setSystemSetting($systemValue);
		$this->assertSame($systemValue, $this->getProjectSetting());

		$this->setProjectSetting($projectValue);
		$this->assertSame($projectValue, $this->getProjectSetting());
	}

	function testSubSettings()
	{
		$_GET['pid'] = TEST_SETTING_PID;

		$groupKey = 'group-key';
		$settingKey = 'setting-key';
		$settingValues = [1, 2];

		$this->setConfig([
			'project-settings' => [
				[
					'key' => $groupKey,
					'type' => 'sub_settings',
					'sub_settings' => [
						[
							'key' => $settingKey
						]
					]
				]
			]
		]);

		$m = $this->getInstance();
		$m->setProjectSetting($settingKey, $settingValues);

		// Make sure prefixing makes the values we just set inaccessible.
		$m->setSettingKeyPrefix('some-prefix');
		$instances = $m->getSubSettings($groupKey);
		$this->assertEmpty($instances);
		$m->setSettingKeyPrefix(null);

		$instances = $m->getSubSettings($groupKey);
		$this->assertSame(count($settingValues), count($instances));
		for($i=0; $i<count($instances); $i++){
			$this->assertSame($settingValues[$i], $instances[$i][$settingKey]);
		}

		$m->removeProjectSetting($settingKey);
	}

	private function assertReturnedSettingType($value, $expectedType)
	{
		// We call set twice to make sure change detection is working properly, and we don't get an exception from trying to set the same value twice.
		$this->setProjectSetting($value);
		$this->setProjectSetting($value);

		$savedValue = $this->getProjectSetting();

		// We check the type separately from assertEquals() instead of using assertSame() because that wouldn't work for objects like stdClass.
		$savedType = gettype($savedValue);
		$this->assertEquals($expectedType, $savedType);
		$this->assertEquals($value, $savedValue);
	}

	function testSettingTypeConsistency()
	{
		$this->assertReturnedSettingType(true, 'boolean');
		$this->assertReturnedSettingType(false, 'boolean');
		$this->assertReturnedSettingType(1, 'integer');
		$this->assertReturnedSettingType(1.1, 'double');
		$this->assertReturnedSettingType("1", 'string');
		$this->assertReturnedSettingType([1], 'array');
		$this->assertReturnedSettingType([1,2,3], 'array');
		$this->assertReturnedSettingType(['a' => 'b'], 'array');
		$this->assertReturnedSettingType(null, 'NULL');

		$object = new \stdClass();
		$object->someProperty = true;
		$this->assertReturnedSettingType($object, 'object');
	}

	function testSettingTypeChanges()
	{
		$this->assertReturnedSettingType('1', 'string');
		$this->assertReturnedSettingType(1, 'integer');
	}

	function testArrayKeyPreservation()
	{
		$array = [1 => 2];
		$this->setProjectSetting($array);
		$this->assertSame($array, $this->getProjectSetting());
	}

	function testArrayNullValues()
	{
		$array = [0 => null];
		$this->setProjectSetting($array);
		$this->assertSame($array, $this->getProjectSetting());
	}

	function testSettingSizeLimit()
	{
		$result = ExternalModules::query("SHOW VARIABLES LIKE 'max_allowed_packet'", []);
		$row = $result->fetch_assoc();
		$maxAllowedPacket = $row['Value'];
		$threshold = $maxAllowedPacket - ExternalModules::SETTING_SIZE_LIMIT+1;
		$allowedThreshold = 1024; // MySQL only allows increasing 'max_allowed_packet' in increments of 1024
		
		if($threshold <= 0){
			// Don't run this test, since it will fail.
			// Skipping the test is safe since max_allowed_packet will cause an error instead of truncation (this test intends to prevent the latter).
			$this->markTestSkipped();
			return;
		}
		else if($threshold < $allowedThreshold){
			$recommendedMaxAllowedPacket = $maxAllowedPacket + $allowedThreshold;
			throw new Exception("Your MySQL server's 'max_allowed_packet' setting is very close to the maximum setting size.  Please increase this value to at least $recommendedMaxAllowedPacket for the " . __FUNCTION__ . "() test to run properly, and to avoid errors when saving large module setting values.");
		}

		$data = str_repeat('a', ExternalModules::SETTING_SIZE_LIMIT);
		$this->setProjectSetting($data);
		$this->assertSame($data, $this->getProjectSetting());

		$this->assertThrowsException(function() use ($data){
			$data .= 'a';
			$this->setProjectSetting($data);
		}, 'value is larger than');
	}

	function testSettingKeySizeLimit()
	{
		$m = $this->getInstance();

		$key = str_repeat('a', ExternalModules::SETTING_KEY_SIZE_LIMIT);
		$value = rand();
		$m->setSystemSetting($key, $value);
		$this->assertSame($value, $m->getSystemSetting($key));
		$m->removeSystemSetting($key);

		$this->assertThrowsException(function() use ($m, $key){
			$key .= 'a';
			$m->setSystemSetting($key, '');
		}, 'key is longer than');
	}

	function testHasPermission()
	{
		$m = $this->getInstance();

		$testPermission = 'some_test_permission';
		$config = ['permissions' => []];

		$this->setConfig($config);
		$this->assertFalse($m->hasPermission($testPermission));

		$config['permissions'][] = $testPermission;
		$this->setConfig($config);
		$this->assertTrue($m->hasPermission($testPermission));
	}

	function testGetUrl()
	{
		$m = $this->getInstance();

		$base = APP_PATH_WEBROOT_FULL . 'external_modules/?prefix=' . $m->PREFIX . '&page=';
		$apiBase = APP_PATH_WEBROOT_FULL . 'api/?type=module&prefix=' . $m->PREFIX . '&page=';
		$moduleBase = ExternalModules::getModuleDirectoryUrl($m->PREFIX, $m->VERSION);

		$this->assertSame($base . 'test', $m->getUrl('test.php'));
		$this->assertSame($base . 'test&NOAUTH', $m->getUrl('test.php', true));
		$this->assertSame($apiBase . 'test', $m->getUrl('test.php', false, true));

		$pid = 123;
		$_GET['pid'] = $pid;
		$this->assertSame($base . 'test&pid=' . $pid, $m->getUrl('test.php'));

		$mTime = filemtime(ExternalModules::getModuleDirectoryPath($m->PREFIX) . '/images/foo.png');
		$this->assertSame($moduleBase . "images/foo.png?$mTime", $m->getUrl('images/foo.png'));
		$this->assertSame($apiBase . 'images%2Ffoo.png', $m->getUrl('images/foo.png', false, true));
	}

	function testGetQueryLogsSql_moduleId()
	{
		$m = $this->getInstance();

		$columnName = 'external_module_id';

		// Make sure that when no where clause is present, a where clause for the current module is added
		$sql = $m->getQueryLogsSql("select log_id");
		$this->assertEquals(1, substr_count($sql, AbstractExternalModule::EXTERNAL_MODULE_ID_STANDARD_WHERE_CLAUSE_PREFIX . " = '" . TEST_MODULE_PREFIX . "')"));

		$moduleId = rand();
		$overrideClause = "$columnName = $moduleId";
		$sql = $m->getQueryLogsSql("select 1 where $overrideClause");

		// Make sure there is only one clause related to the module id.
		$this->assertEquals(1, substr_count($sql, $columnName));

		// Make sure our override clause has replaced the the clause for the current module.
		$this->assertEquals(1, substr_count($sql, $overrideClause));
	}

	function testGetQueryLogsSql_overrideProjectId()
	{
		$m = $this->getInstance();

		$columnName = 'project_id';

		// Make sure that when no where clause is present, a where clause for the current project is added
		$projectId = '1';
		$_GET['pid'] = $projectId;
		$sql = $m->getQueryLogsSql("select log_id");
		$this->assertEquals(1, substr_count($sql, "$columnName = $projectId"));

		$projectId = '2';
		$overrideClause = "$columnName = $projectId";
		$sql = $m->getQueryLogsSql("select 1 where $overrideClause");

		// Make sure there is only one clause related to the project id.
		$this->assertEquals(1, substr_count($sql, $columnName));

		// Make sure our override clause has replaced the the clause for the current project.
		$this->assertEquals(1, substr_count($sql, $overrideClause));
	}

	function testExceptionOnMissingMethod()
	{
		// We use the __call() magic method, which prevents the default missing method error.
		// The following asserts that we are throwing our own exception from __call().
		$this->assertThrowsException(function(){
			$m = $this->getInstance();
			$m->someMethodThatDoesntExist();
		}, 'method does not exist');
	}

	function testGetSubSettings()
	{
		$pid = TEST_SETTING_PID;
		$_GET['pid'] = $pid;
		$m = $this->getInstance();

		$settingValues = [
			// Make sure the first setting is no longer being used to detect any lengths by simulating a new/empty setting.
			'key1' => [],

			// These settings each intentionally have difference lengths to make sure they're still returned appropriately.
			'key2' => ['a', 'b', 'c'],
			'key3' => [1,2,3,4,5],
			'key4' => [true, false]
		];

		$subSettingsConfig = [];
		foreach($settingValues as $key=>$values){
			$m->setProjectSetting($key, $values);

			$subSettingsConfig[] = [
				'key' => $key
			];
		}

		$subSettingsKey = 'sub-settings-key';
		$this->setConfig([
			'project-settings' => [
				[
					'key' => $subSettingsKey,
					'type' => 'sub_settings',
					'sub_settings' => $subSettingsConfig
				]
			]
		]);

		$assertSubSettings = function($pid) use ($m, $subSettingsKey, $settingValues) {
			$subSettingResults = $m->getSubSettings($subSettingsKey, $pid);
			foreach($settingValues as $key=>$values){
				for($i=0; $i<count($values); $i++){
					$this->assertSame($settingValues[$key][$i], $subSettingResults[$i][$key]);
				}
			}
		};

		$assertSubSettings(null);

		unset($_GET['pid']);

		$this->assertThrowsException(function() use ($assertSubSettings) {
			$assertSubSettings(null);
		}, 'argument to this method: pid');

		$assertSubSettings($pid);
	}

	function testSetSetting_concurrency()
	{
		// This test spins off a second phpunit process in order to test concurrency and locking in setSetting().
		// If you comment out the GET_LOCK call in setSetting(), an exception should occur within a fraction of $maxIterations.
		$iterations = 0;
		$maxIterations = 1000;

		$concurrentOperations = function(){
			$this->setProjectSetting('some value');
			$this->removeProjectSetting();
		};

		$parentAction = function ($isChildRunning) use ($concurrentOperations, $iterations, $maxIterations) {
			while($isChildRunning()){
				$concurrentOperations();
				$iterations++;
			}

			// The parent will generally run more iterations than the child, but apparently not always.
			// Consider the text successful if $iterations is at least 90% of $maxIterations.
			$this->assertGreaterThan($maxIterations * 0.9, $iterations);
		};

		$childAction = function () use ($iterations, $maxIterations, $concurrentOperations) {
			while ($iterations < $maxIterations) {
				$concurrentOperations();
				$iterations++;
			}

			$this->assertSame($iterations, $maxIterations);
		};

		$this->runConcurrentTestProcesses(__FUNCTION__, $parentAction, $childAction);
	}

	function testSetSetting_projectDesignRights()
	{
		ExternalModules::setSuperUser(false);

		$m = $this->getInstance();
		$fieldName = 'project';
		$pid = $_GET['pid'] = TEST_SETTING_PID;
		$pidWithRights = TEST_SETTING_PID_2;

		$this->setConfig([
			'system-settings' => [
				[
					'key' => $fieldName,
					'type' => 'project-id'
				]
			]
		]);

		$username = $this->getUsernameNotOnProject($pid);

		// Make the username lowercase because some usernames are stored with a capitalized first character (ex: 'Crenshd'),
		// even though REDCap functions like UserRights::getPrivileges() expect them to be all lowercase.
		$username = strtolower($username);
		
		ExternalModules::setUsername($username);

		$addToProject = function($pid, $design) use ($m, $username){
			$m->framework->getProject($pid)->addUser($username, [
				'design' => $design,
			]);
		};

		$addToProject($pid, 0);
		$addToProject($pidWithRights, 1);
		
		$assert = function($exceptionExpected, $oldValue, $newValue) use ($m, $fieldName, $username){
			$action = function() use ($m, $fieldName, $oldValue, $newValue){
				ExternalModules::setSuperUser(true);
				$m->setProjectSetting($fieldName, $oldValue);

				ExternalModules::setSuperUser(false);
				$m->setProjectSetting($fieldName, $newValue);
			};

			// change to assert?
			// The try/catch is only to print the username used on failure.
			try{
				if($exceptionExpected){
					$this->assertThrowsException($action, 'do not have design rights');
				}
				else{
					$action();
				}
			}
			catch(Exception $e){
				throw new Exception("Error running test for username: $username", 0, $e);
			}
		};

		// Test a few different sub-setting structures for good measure.
		$values = [
			$pid,
			[$pid, $pidWithRights],
			[$pidWithRights, $pid],
			[[$pid, $pidWithRights], [$pidWithRights, $pidWithRights]],
			[[$pidWithRights, $pidWithRights], [$pidWithRights, $pid]],
		];

		try{
			foreach($values as $value){
				$valueWithRights = json_decode(str_replace($pid, $pidWithRights, json_encode($value)), true);

				$assert(false, null, $valueWithRights);
				$assert(true, null, $value);
				$assert(false, $value, $valueWithRights);
				$assert(true, $valueWithRights, $value);
				
				if(is_array($value)){
					// Adding instances should work if you have access to the ones you're adding (regardless of existing instances).
					$assert(false, $value, array_merge($value, $valueWithRights));
					$assert(true, $value, array_merge($value, $value));
					
					// Removing instances should always work.
					$assert(false, array_merge($value, $value), $value);
				}
			}
		}
		finally{
			ExternalModules::removeUserFromProject($pid, $username);
			ExternalModules::removeUserFromProject($pidWithRights, $username);
		}
	}

	private function getUsernameNotOnProject($pid){
		$rights = \UserRights::getPrivileges($pid)[$pid];
		$usernames = array_keys((array) $rights);
		
		$count = 0;
		while(true){
			$username = $this->getRandomUsername();
			if(!in_array($username, $usernames)){
				break;
			}

			$count++;
			if($count > 10){
				throw new Exception("An error occurred while trying to find a user that wasn't on project $pid.");
			}
		}

		return $username;
	}

	function testMultipleDAGMethods(){
		$_GET['pid'] = TEST_SETTING_PID;

		$getName = function($id){
			$result = ExternalModules::query('select group_name from redcap_data_access_groups s where project_id = ? and group_id = ?', [TEST_SETTING_PID, $id]);
			return $result->fetch_assoc()['group_name'];
		};

		$m = $this->getInstance();
		$name = 'test dag ' . rand();
		$id = $m->createDag($name);
		
		$this->assertSame($name, $getName($id));

		$name .= '-2';
		$m->renameDAG($id, $name);
		$this->assertSame($name, $getName($id));

		$m->deleteDAG($id);
		$this->assertNull($getName($id));
	}

	function testGetProjectAndRecordFromHashes(){
		$m = $this->getInstance();

		$result = $m->query("
			SELECT s.project_id as projectId, r.record as recordId, s.form_name as surveyForm, p.event_id as eventId,
				p.hash, r.return_code
			FROM redcap_surveys_participants p, redcap_surveys_response r, redcap_surveys s
			WHERE p.survey_id = s.survey_id
				AND p.participant_id = r.participant_id
				and return_code is not null
			ORDER BY p.participant_id DESC
			LIMIT 1
		", []);

		$expected = $result->fetch_assoc();
		$actual = $m->getProjectAndRecordFromHashes($expected['hash'], $expected['return_code']);

		$fieldNames = [
			'projectId',
			'recordId',
			'surveyForm',
			'eventId'
		];

		foreach($fieldNames as $fieldName){
			$this->assertSame($expected[$fieldName], $actual[$fieldName]);
		}
	}

	function testGetProjectDetails(){
		$m = $this->getInstance();
		$details = $m->getProjectDetails(TEST_SETTING_PID);

		$this->assertSame(TEST_SETTING_PID, $details['project_id']);
		$this->assertGreaterThan(100, count($details));
	}

	function testSetData(){
		$_GET['pid'] = TEST_SETTING_PID;
		$_GET['event_id'] = $this->getInstance()->framework->getEventId();
		$_GET['instance'] = 1;
		$recordId = 1;

		$result = $this->query("
			select field_name
			from redcap_metadata
			where
				project_id = ?
				and field_order = ?
				and field_name not like '%_complete'
		", [TEST_SETTING_PID, 2]);

		$fieldName = $result->fetch_row()[0];
		if(empty($fieldName)){
			throw new Exception("You must add a field to the External Module test project with ID " . TEST_SETTING_PID);
		}

		$value = (string) rand();

		$this->ensureRecordExists($recordId);
		
		$this->setData($recordId, $fieldName, $value);

		$data = json_decode(REDCap::getData(TEST_SETTING_PID, 'json', $recordId), true)[0];

		$this->assertSame($value, $data[$fieldName]);
	}

	function __get($varName){
		return $this->getInstance()->$varName;
	}

	function testAddAutoNumberedRecord(){
		$_GET['pid'] = TEST_SETTING_PID;

		$recordId1 = $this->addAutoNumberedRecord();
		$recordId2 = $this->addAutoNumberedRecord();

		$this->assertSame($recordId1+1, $recordId2);

		$this->deleteRecords(TEST_SETTING_PID, [$recordId1, $recordId2]);
	}

	function deleteRecord($pid, $recordId){
		$this->deleteRecords($pid, [$recordId]);
	}

	function deleteRecords($pid, $recordIds){
		$q = $this->framework->createQuery();
		$q->add('delete from redcap_data where project_id = ? and', [$pid]);
		$q->addInClause('record', $recordIds);
		$q->execute();

		$this->assertSame(count($recordIds), $q->affected_rows);
	}

	function testGenerateUniqueRandomSurveyHash(){
		$hash = $this->generateUniqueRandomSurveyHash();
		$this->assertSame(10, strlen($hash));
	}

	function testGetParticipantAndResponseId(){
		list($surveyId, $formName) = $this->getFramework()->getSurveyId(TEST_SETTING_PID);

		$recordId = 1;
		$participantId = (string) $this->getParticipantId($surveyId, $this->getFramework()->getEventId(TEST_SETTING_PID));
		$responseId = (string) $this->getSurveyResponse($participantId, $recordId);
		
		$this->assertSame([$participantId, $responseId], $this->getParticipantAndResponseId($surveyId, $recordId));
	}

	function testGetParticipantAndResponseId_notFound(){
		$_GET['pid'] = TEST_SETTING_PID;
		$this->assertSame([null, null], $this->getParticipantAndResponseId(99999999999, 99999999999));
	}

	private function getParticipantId($surveyId, $eventId){
		$result = $this->query('
			select participant_id
			from redcap_surveys_participants
			where
				survey_id = ?
				and event_id = ?
			order by participant_id asc
			limit 1
		', [$surveyId, $eventId]);

		$row = $result->fetch_assoc();
		if($row === null){
			return ExternalModules::addSurveyParticipant($surveyId, $eventId, $this->generateUniqueRandomSurveyHash());
		}

		return $row['participant_id'];
	}

	private function getSurveyResponse($participantId, $recordId){
		$result = $this->query('
			select response_id
			from redcap_surveys_response
			where
				participant_id = ?
				and record = ?
		', [$participantId, $recordId]);

		$row = $result->fetch_assoc();
		if($row === null){
			return ExternalModules::addSurveyResponse($participantId, $recordId, generateRandomHash());
		}
		
		return $row['response_id'];
	}

	function testDelayModuleExecution()
	{
        $exceptionThrown = false;
        $throwException = function($message) use (&$exceptionThrown){
            $exceptionThrown = true;
            throw new Exception($message);
        };

        $hookExecutionsExpected = 3;
        $executionNumber = 0;
        $delayTestFunction = function($module) use (&$executionNumber, $hookExecutionsExpected, $throwException){
            $hookRunner = $this->callPrivateMethodForClass(ExternalModules::class, 'getCurrentHookRunner');

            // The delay queue should be empty at the beginning of each call.
            $this->assertEmpty($hookRunner->getDelayed());

			$delaySuccessful = $module->delayModuleExecution();
            $executionNumber++;

            if($executionNumber < $hookExecutionsExpected){
                if(!$delaySuccessful){
                    $throwException("The first hook run and the first attempt at re-running after delaying should both successfully delay.");
                }
            }
            else if($executionNumber == $hookExecutionsExpected){
                if($delaySuccessful){
                    $throwException("The final run that gives modules a last chance to run if they have been delaying should NOT successfully delay.");
                }
            }
        };

		ExternalModules::callHook('redcap_test_call_function', [$delayTestFunction]);
        $this->assertFalse($exceptionThrown);
		$this->assertEquals($hookExecutionsExpected, $executionNumber);
	}

	/**
	 * New AbstractExternalModule methods can potentially conflict with module code, sometimes even crashing the REDCap server.
	 * This test ensures that new methods are only added to the Framework class going forward,
	 * and that old methods are left in place for backward compatibility (including method_exists() calls).
	 */
	function testNoNewMethodsAdded(){
		$expectedMethods = [
			ReflectionMethod::IS_PUBLIC => [
				"__call",
				"__construct",
				"__get",
				"addAutoNumberedRecord",
				"areSettingPermissionsUserBased",
				"createDAG",
				"createPassthruForm",
				"delayModuleExecution",
				"deleteDAG",
				"disableUserBasedSettingPermissions",
				"exitAfterHook",
				"generateUniqueRandomSurveyHash",
				"getChoiceLabel",
				"getChoiceLabels",
				"getConfig",
				"getData",
				"getFieldLabel",
				"getFirstEventId",
				"getMetadata",
				"getModuleDirectoryName",
				"getModuleName",
				"getModulePath",
				"getParticipantAndResponseId",
				"getProjectAndRecordFromHashes",
				"getProjectDetails",
				"getProjectId",
				"getProjectSetting",
				"getProjectSettings",
				"getPublicSurveyHash",
				"getPublicSurveyUrl",
				"getQueryLogsSql",
				"getRecordId",
				"getRecordIdOrTemporaryRecordId",
				"getSettingConfig",
				"getSubSettings",
				"getSurveyId",
				"getSystemSetting",
				"getSystemSettings",
				"getUrl",
				"getUserSetting",
				"getValidFormEventId",
				"hasPermission",
				"init",
				"initializeJavascriptModuleObject",
				"isSurveyPage",
				"logAjax",
				"prefixSettingKey",
				"query",
				"queryLogs",
				"redcap_module_configure_button_display",
				"redcap_module_link_check_display",
				"removeLogs",
				"removeProjectSetting",
				"removeSystemSetting",
				"removeUserSetting",
				"renameDAG",
				"requireProjectId",
				"resetSurveyAndGetCodes",
				"saveData",
				"saveFile",
				"saveInstanceData",
				"sendAdminEmail",
				"setDAG",
				"setData",
				"setProjectSetting",
				"setProjectSettings",
				"setRecordId",
				"setSystemSetting",
				"setUserSetting",
				"validateSettings"
			],
			ReflectionMethod::IS_PROTECTED => [
				"checkSettings",
				"getSettingKeyPrefix",
				"isSettingKeyValid"
			],
			ReflectionMethod::IS_STATIC => [
				"init"
			]
		];

		$class = new ReflectionClass('ExternalModules\AbstractExternalModule');

		$actual = [];
		foreach($expectedMethods as $visibility=>$expected){
			$actual = array_column($class->getMethods($visibility), 'name');

			sort($actual);
			
			// JSON encode results for a better diff on failure
			$this->assertSame(
				json_encode($expected, JSON_PRETTY_PRINT),
				json_encode($actual, JSON_PRETTY_PRINT),
			);
		}
	}

	function testRequireProjectId(){
		$this->assertThrowsException(function(){
			$this->requireProjectId();
		}, 'You must supply');

		$value = rand();
		$this->assertSame($value, $this->requireProjectId($value));

		$_GET['pid'] = $value;
		$this->assertSame($value, $this->requireProjectId(null));
	}
}
