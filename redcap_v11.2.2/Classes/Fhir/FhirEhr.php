<?php

namespace Vanderbilt\REDCap\Classes\Fhir;

use User;
use Vanderbilt\REDCap\Classes\Fhir\FhirServices;
use Vanderbilt\REDCap\Classes\Fhir\FhirClient;
use Vanderbilt\REDCap\Classes\Fhir\FhirCategory;
use Vanderbilt\REDCap\Classes\Fhir\TokenManager\FhirTokenManager;
use Vanderbilt\REDCap\Classes\Fhir\TokenManager\FHIRToken;
use Vanderbilt\REDCap\Classes\Fhir\Endpoints\AbstractEndpoint;
use Vanderbilt\REDCap\Classes\Fhir\FhirLauncher;
use Vanderbilt\REDCap\Classes\Fhir\Resources\Shared\Patient;

class FhirEhr
{	
	// Other FHIR-related settings
	private static $fhirRedirectPage = 'ehr.php';
	public $ehrUIID = null;
	public  $fhirPatient = null; // Current patient
	public  $fhirAccessToken = null; // Current FHIR access token
	public $fhirResourceErrors = array();

	/**
	 * list of keys that must not be shown on the EHR error page
	 * and in the logs table
	 */
	private static $sensitive_data_keys = array('Authorization');

	/**
	 * Standard Standalone Launch authentication flow
	 */
	const AUTHENTICATION_FLOW_STANDARD = 'standalone_launch';
	/**
	 * OAuth2 client credentials authentication flow (cerner only)
	 */
	const AUTHENTICATION_FLOW_CLIENT_CREDENTIALS = 'client_credentials';

	
	// Construct
	public function __construct()
	{
		// Start the session if not started
		\Session::init();
		// Initialization check to ensure we have all we need
		$this->initCheck();
	}

	/**
	 * listen for user actions
	 * TODO: move to it's own controller
	 * - force destroy session data
	 * - create a patient record
	 * - add a project 
	 */
	public function listenForUserActions()
	{		
		// listen for Add/remove project from Registered Project list
		$this->checkAddRemoveProject();

		// detect if user wants to clear session data
		if (isset($_SESSION['username']) && isset($_POST['action']) && $_POST['action'] == 'destroy_fhir_session')
		{
			FhirLauncher::cleanup();
			$response = array('success' => true);
			print json_encode($response);
			exit;
		}

		// detect if we are creating a record for a patient
		if (isset($_SESSION['username']) && defined("PROJECT_ID") && isset($_POST['action']) && $_POST['action'] == 'create_record')
		{
			$patientMrn = $_POST['mrn'];
			$this->createPatientRecord($patientMrn);
			exit;
		}
	}
	
	// Set UI_ID contant (if not set)
	public function setUiId()
	{
	    global $lang, $homepage_contact_email;
		if (isset($this->ehrUIID) && is_numeric($this->ehrUIID)) return true;
		// Determine UI_ID from the username
		if (defined("USERID")) {
			$userInfo = \User::getUserInfo(USERID);
			$this->ehrUIID = $userInfo['ui_id'];
			// If user is suspended, then stop here with an error
			if ($userInfo['user_suspended_time'] != '') {
			    $msg = "{$lang['global_01']}{$lang['colon']} {$lang['config_functions_75']} <b>".USERID."</b>{$lang['period']}
					    {$lang['config_functions_76']} $homepage_contact_email</a>{$lang['period']}";
				exit($msg);
			}
		}
		/**
		 * The cron will not have a user session or
		 * have USERID set; the FhirTokenManager will select the
		 * best token available
		 */
		if (defined("CRON")) {
			$this->ehrUIID = null;
		}
		// Set UI_ID (but not for cron jobs)
		if (!defined("UI_ID") && !defined("CRON") && is_numeric($this->ehrUIID)) {
			define("UI_ID", $this->ehrUIID);
		}
		// Return boolean on if UI_ID is valid
		return is_numeric($this->ehrUIID);
	}
	
	// Set username constant
	public function setUserId()
	{
		if (!isset($_SESSION['username'])) return;
		defined("USERID") or define("USERID", strtolower($_SESSION['username']));
	}
	
	// Return variable name of field having MRN data type
	private function getFieldWithMrnValidationType()
	{
		global $Proj;
		$mrnValTypes = $this->getMrnValidationTypes();
		foreach ($Proj->metadata as $field=>$attr) {
			if ($attr['element_validation_type'] == '') continue;
			if (!isset($mrnValTypes[$attr['element_validation_type']])) continue;
			return $field;
		}
		return false;
	}
	
	// Return array of field validation types with MRN data type
	private function getMrnValidationTypes()
	{
		$mrnValTypes = array();
		$valTypes = getValTypes();
		foreach ($valTypes as $valType=>$attr) {
			if ($attr['data_type'] != 'mrn') continue;
			$mrnValTypes[$valType] = $attr['validation_label'];
		}
		return $mrnValTypes;
	}
	
	// Return the form_name and event_id of the DDP MRN field or the MRN data type field in the current project (if multiple, then return first)
	private function getFormEventOfMrnField()
	{
		global $Proj;
		// If DDP is enabled, then get DDP mapped MRN field
		$DDP = new \DynamicDataPull($Proj->project_id, $Proj->project['realtime_webservice_type']);
		if ($DDP->isEnabledInSystemFhir() && $DDP->isEnabledInProjectFhir()) {
			list ($field, $event_id) = $DDP->getMappedIdRedcapFieldEvent();
		} else {
			$field = $this->getFieldWithMrnValidationType();
			$event_id = $this->getFirstEventForField($field);
		}
		return array($field, $event_id);
	}
	
	// Return the event_id where a field's form_name is first used in a project
	private function getFirstEventForField($field)
	{
		global $Proj;
		// Get field's form
		$form = $Proj->metadata[$field]['form_name'];
		// Loop through events to find first event to which this form is designated
		foreach ($Proj->eventsForms as $event_id=>$forms) {
			if (!in_array($form, $forms)) continue;
			return $event_id;
		}
		return $Proj->firstEventId;
	}
	
	// Create new record for patient in project
	private function createNewPatientRecord($newRecord, $mrn)
	{
		global $Proj;
		// Find the form and event where the MRN field is located
		list ($mrnField, $mrnEventId) = $this->getFormEventOfMrnField();
		// Make sure record doesn't already exist
		if (\Records::recordExists(PROJECT_ID, $newRecord)) exit("ERROR: Record \"$newRecord\" already exists in the project. Please try another record name.");
		// Add record as 2 data points: 1) record ID field value, and 2) MRN field value
		$sql = "insert into redcap_data (project_id, event_id, record, field_name, value) values
				(".PROJECT_ID.", $mrnEventId, '".db_escape($newRecord)."', '".db_escape($Proj->table_pk)."', '".db_escape($newRecord)."'),
				(".PROJECT_ID.", $mrnEventId, '".db_escape($newRecord)."', '".db_escape($mrnField)."', '".db_escape($mrn)."')";
		$q = db_query($sql);
		if ($q) {
			// Logging
			defined("USERID") or define("USERID", strtolower($_SESSION['username']));
			\Logging::logEvent($sql, "redcap_data", "INSERT", $newRecord, "{$Proj->table_pk} = '$newRecord',\n$mrnField = '$mrn'", "Create record");
		}
		// Return boolean for success
		return $q;
	}

	/**
	 * create a record with the new patient
	 *
	 * @param string $patientMrn
	 * @return void
	 */
	private function createPatientRecord($patientMrn)
	{
		global $lang, $auto_inc_set;
		if (empty($patientMrn)) exit("ERROR: Did not receive the MRN!");
		// Get record and MRN values
		$newRecord = $auto_inc_set ? \DataEntry::getAutoId() : $_POST['record'];
		if ($newRecord == '') exit("ERROR: Could not determine the record name for the project!");			
		// Create new record for patient in project
		if ($this->createNewPatientRecord($newRecord, $patientMrn)) {				
			global $Proj;
			$errors = "";
			// If DDP-FHIR is enabled in the project, then go ahead and trigger DDP to start importing data
			if ($Proj->project['realtime_webservice_enabled'] && $Proj->project['realtime_webservice_type'] == 'FHIR') 
			{
				// Fetch DDP data from EHR
				$DDP = new \DynamicDataPull($Proj->project_id, $Proj->project['realtime_webservice_type']);
				list($itemsToAdjudicate, $html) = $DDP->fetchAndOutputData($newRecord, null, array(), $Proj->project['realtime_webservice_offset_days'], 
													$Proj->project['realtime_webservice_offset_plusminus'], false);
				// Any errors?
				if (isset($this->fhirResourceErrors) && !empty($this->fhirResourceErrors)) {
					$errors = "<div class='red' style='margin:10px 0;'><b><i class='fas fa-exclamation-triangle'></i> {$lang['global_03']}{$lang['colon']}</b> {$lang['ws_246']}<ul style='margin:0;'><li>".implode("</li><li>", $this->fhirResourceErrors)."</li></ul></div>";
				}
			}
			// Text to display in the dialog
			print $lang['data_entry_384'] . $errors;
			// Also add hidden field as the new record name value
			print \RCView::hidden(array('id'=>'newRecordCreated', 'value'=>$newRecord));
			// Add note about how many DDP items there are to adjudicate (if any)
			if (isset($itemsToAdjudicate) && $itemsToAdjudicate > 0) {
				print 	\RCView::div(array('style'=>'color:#C00000;margin-top: 10px;'),
							\RCView::a(array('href'=>APP_PATH_WEBROOT."DataEntry/record_home.php?pid={$Proj->project_id}&id={$newRecord}&openDDP=1", 'style'=>'color:#C00000;'), 
								\RCView::span(array('class'=>'badgerc'), $itemsToAdjudicate) . $lang['data_entry_378']
							)
						);
			}
		} else {
			exit("ERROR: There was an error creating a new record.");
		}
	}

	// Capture EHR user and add it to session if "user" param is in launch query string
	public function getEhrUserFromUrl()
	{
		global $fhir_endpoint_base_url;
		$testWebsitesUsers = array(
			// 'smarthealthit.org' => 'SMART_FAKE_USER',
			'open-ic.epic.com' => 'OPEN_EPIC_FAKE_USER',
		);
		foreach ($testWebsitesUsers as $url => $user) {
			$regExp = sprintf('/%s/i', preg_quote($url, '/'));
			if(preg_match($regExp, $fhir_endpoint_base_url)) return $user;
		}

		// change all key to lowercase to get both user or USER
		$_GET_lower = array_change_key_case($_GET, CASE_LOWER);
		if($user = trim(rawurldecode(urldecode($_GET_lower['user']))))
		{
			return $user;
		}
	}


	/**
	 * store a token in the database
	 *
	 * @param array $token
	 * @param string $user_id
	 * @return boolean
	 */
	private function storeToken($token, $user_id)
	{
		if(!empty($user_id))
		{
			$savedToken = FhirTokenManager::storeToken($token, $user_id);
			return !empty($savedToken);
		}else
		{
			throw new \Exception("Error storing the token: you are not logged in.", 1);	
		}
	}

	/**
	 * get patient resource
	 *
	 * @param string $patient_id
	 * @param string $user_id
	 * @throws Exception
	 * @return array [FirstName,LastName,BirthDate,ID,MRN,identifiers]
	 */
	private function getPatientData($patient_id, $user_id)
	{

		try {

			$patient_id = urldecode($_GET['fhirPatient']);
			// show the portal if a patient  ID is provided
			$tokenManager = new FhirTokenManager($user_id, $patient_id);
			$fhirClient = new FhirClient($project_id=null, $tokenManager);
			$endpointFactory = $fhirClient->getFhirVersionManager()->getEndpointFactory();
			$request = $endpointFactory->make(FhirCategory::DEMOGRAPHICS, $patient_id, AbstractEndpoint::INTERACTION_READ);
			/** @var Patient $patient */
			$patient = $fhirClient->getResource($request);

			$institutionMrn = $patient->getIdentifier($GLOBALS['fhir_ehr_mrn_identifier']);
			$identifiers = $patient->getIdentifiers();
			if(!empty($institutionMrn)) {
				$tokenManager->removeMrnDuplicates($institutionMrn);
				$tokenManager->storePatientMrn($patient_id, $institutionMrn);
			}
			$patientData = [
				'FirstName' => $patient->getNameGiven(),
				'LastName' => $patient->getNameFamily(),
				'BirthDate' => $patient->getBirthDate(),
				'ID' => $patient->getFhirID(),
				'MRN' => $institutionMrn,
				'identifiers' => $identifiers,
			];
			return $patientData;
		} catch (\Exception $e) {
			$data = array(
				'patient_id' => $patient_id,
			);
			throw new \DataException($e->getMessage(), $data, $e->getCode());
		}
	}

	// Perform FHIR launch via EHR
	public function launchFromEhr()
	{
		global $fhir_endpoint_base_url, $fhir_client_id, $fhir_client_secret, $fhir_standalone_authentication_flow;

		// Instantiate FHIR Services
		$fhir_services = self::getFhirServices();
		$redirect_uri = self::getFhirRedirectUrl();
		// create a launcher
		$launcher = new FhirLauncher($fhir_services, $redirect_uri);
		$launcher->checkAutoLogin(); // check if an autologin can be performed

		$mode = $launcher->getMode();

		// session data must be empty when starting a launch
		if(in_array($mode, FhirLauncher::$launch_modes)) FhirLauncher::cleanup();
		if(in_array($mode, FhirLauncher::$protected_modes))
		{
			// user must be logged in to proceed
			$user_id = self::getUserID();
			if(empty($user_id)) loginFunction(); 
		}

		try {
			switch ($mode) {
				case FhirLauncher::MODE_CLIENT_CREDENTIALS:
					$token = $launcher->clientCredentialFlow($fhir_services);
					$launcher->processToken($token);
					$user_id = self::getUserID();
					if($this->storeToken($token, $user_id))
					{
						\Logging::logEvent( "", "FHIR", "MANAGE", "", "", "FHIR Token info saved to database.");
					}
					$launch_page = $launcher->getLaunchPage();
					$app_path_webroot_full = APP_PATH_WEBROOT_FULL;
					return print \Renderer::run('ehr.token', compact('token', 'launch_page', 'app_path_webroot_full'));
					break;
				case FhirLauncher::MODE_STANDALONE_LAUNCH:
					\Logging::logEvent( "", "FHIR", "MANAGE", "", \Logging::printArray($_GET), "Starting standalone launch" );
					$launcher->standaloneLaunchFlow();
					break;
				case FhirLauncher::MODE_AUTHORIZE:
					// Log event $sql, $object_type, $event, $record, $data_values, $description
					\Logging::logEvent( "", "FHIR", "MANAGE", "", \Logging::printArray($_GET), "Received launch code from EHR" );
					// the launch code is used to get an authorization code
					$launcher->authorize();
					break;
				case FhirLauncher::MODE_TOKEN:
					// the authorization code is used to get an access token 
					\Logging::logEvent( "", "FHIR", "MANAGE", "", \Logging::printArray($_GET), "Exchanging FHIR code for authorization token" );
					$token = $launcher->getToken();
					$launcher->processToken($token);
					$user_id = self::getUserID();
					if($this->storeToken($token, $user_id))
					{
						\Logging::logEvent( "", "FHIR", "MANAGE", "", "", "FHIR Token info saved to database.");
					}
					// launch is over and token is stored
					if($patient_fhir_id = $token->patient)
					{
						// have patient ID; rto the launch page and show the portl
						$redirect_url = $_SERVER['PHP_SELF']."?fhirPatient=".urlencode($patient_fhir_id);
						\HttpClient::redirect($redirect_url);
					}else
					{
						// no patient available in the token;
						// we are probably here after a standalone launch
						$launch_page = $launcher->getLaunchPage();
						$app_path_webroot_full = APP_PATH_WEBROOT_FULL;
						return print \Renderer::run('ehr.token', compact('token', 'launch_page', 'app_path_webroot_full'));
					}
					break;
				case FhirLauncher::MODE_ERROR:
					$error = $launcher->getError();
					if($error->url)
					{
						$error_link = sprintf('<a href="%s" target="_BLANK">%s</a>', $error->url, $error->message);
						$data = array('error_link' => $error_link);
					}else {
						$data = array('error' => $error->message);
					}
					throw new \DataException("Error Processing Request", $data, 1);
					break;
				case FhirLauncher::MODE_SHOW_PORTAL:
					$patient_id = $_GET['fhirPatient'];
					$patientData = $this->getPatientData($patient_id, $user_id);
					$launcher->setSessionData('patient-data', $patientData); // will be used in portal and navbar
					return $this->renderFhirPortal($patientData);
					break;
				case FhirLauncher::MODE_NONE:
				default:
						//show the default page
						$authentication_flow = $fhir_standalone_authentication_flow;
						$app_path_webroot= APP_PATH_WEBROOT;
						return print \Renderer::run('ehr.index', compact('authentication_flow', 'app_path_webroot'));
					break;
			}
		} catch (\Exception $e) {
			$exception_code = $e->getCode();
			$exception_message = $e->getMessage();
			$exception_data = array();
			if($e instanceof \DataException) $exception_data = $e->getData();

			// SEARCH AND ANONIMIZE SENSITIVE DATA
			$cleaned_exception_data = self::removeSensitiveData($exception_data, self::$sensitive_data_keys);
			$encoded_exception_data = strval(json_encode($cleaned_exception_data, JSON_PRETTY_PRINT));
			\Logging::logEvent( "", "FHIR", "MANAGE",  $encoded_exception_data, $exception_code, $exception_message );
			$launcher->cleanup(); // cleanup

			$variables = array(
				'code' => $exception_code,
				'message' => $exception_message,
				'data' => $cleaned_exception_data,
			);
			print \Renderer::run('ehr.error', $variables);
		}
		return;
	}

	/**
	 * remove sensitive data from an array
	 * looking for specific keys
	 *
	 * @param array $data
	 * @param array $keys
	 * @return array data with no sensitive data
	 */
	private static function removeSensitiveData($data, $keys)
	{
		// helper function:
		// anonimize matched keys
		$anonimize = function(&$item, $key) use($keys)
		{
			foreach($keys as $protected_key)
			{
				$regexp = "/{$protected_key}/i";
				if(preg_match($regexp, $key, $matches))
				{
					$length = strlen($item);
					$item = str_repeat('*', $length);
				}
			}
		};
		array_walk_recursive($data, $anonimize);
		return $data;
	}
	
	/**
	 * Return the institution-specific version of the MRN (e.g., $system_string='urn:oid:1.2.5.8.2.7' for Vanderbilt MRN)
	 *
	 * @param Patient $patient
	 * @param string $system_string
	 * @return string|false
	 */
	private function getPatientMrnFromPatientData($patient, $system_string='')
	{
		if (!empty($system_string) && $patient instanceof Patient) {
			return $patient->getIdentifier($system_string);
		}
		return false;
	}

	public static function getUserID()
	{
		if ($GLOBALS['auth_meth_global'] == 'none') {
			$_SESSION['username'] = 'site_admin';
		}
		\Session::init();
		if (!isset($_SESSION['username'])) return;
		if(!defined("USERID")) define("USERID", strtolower($_SESSION['username']));
		$user_id = \User::getUIIDByUsername(USERID);
		/* $user_info = (object)\User::getUserInfo($id=USERID);
		$user_id = $user_info->ui_id; */
		return $user_id;
	}
	
	/**
	 * Display page with EHR user and patient in context
	 *
	 * @param array $patientData [FirstName,LastName,BirthDate,ID,MRN,identifiers]
	 * @return void
	 */
	private function renderFhirPortal($patientData)
	{
		global $lang;
		// retrieve patient data from the session
		if(empty($patientData))
			throw new \Exception("Error: no patient data has been found", 1);
		
        $patientID = @$patientData['ID'];
        // Get institution-specific MRN
		$patientMrn = @$patientData['MRN'];
		if(empty($patientMrn)) $patientMrn = $patientID; // use patient ID if no MRN is found
		
		// Get array of MRN field validation types
		$mrnValidationTypes = $this->getMrnValidationTypes();
		$user_id = self::getUserID();
		
		// Create arrays of registered and unregistered projects for the current user
		$registeredProjects = $this->setRegisteredProjects($patientMrn, $user_id);
		$unregisteredProjects = $this->setUnegisteredProjects($user_id);
			
		// Render page and navbar
		$HtmlPage = new \HtmlPage();
		$HtmlPage->PrintHeaderExt();
		$this->renderNavBar($patientData);

		$variables = array(
			'patientID' => $patientID,
			'patientMrn' => $patientMrn,
			'lang' => $lang,
			'registeredProjects' => $registeredProjects,
			'unregisteredProjects' => $unregisteredProjects,
			'app_path_webroot' => APP_PATH_WEBROOT,
			'mrnValidationTypes' => $mrnValidationTypes,
		);
		$variables['modifyScriptURL'] = "";
		if(empty($_GET['fhirPatient']))
		{
			// update the URL
			$symbol = strpos($_SERVER['REQUEST_URI'], '?') ? '&' : '?'; //symbol to connect patient ID with URL
			$modifyScriptURL = "{$_SERVER['REQUEST_URI']}{$symbol}fhirPatient={$patientID}";
			$variables['modifyScriptURL'] = $modifyScriptURL;
		}
		print \Renderer::run('ehr.portal', $variables);

		$userInfo = User::getUserInfoByUiid($user_id);
		$username = @$userInfo['username'];
		$isSuperUser = User::isSuperUser($username);
		
		if($isSuperUser) {
			$identifiers = @$patientData['identifiers'] ?: [];
			print \Renderer::run('ehr.patient-identifier', array('identifiers' => $identifiers));
		}
		// Footer
		$HtmlPage->PrintFooterExt();
	}
	
	/**
	 * Render navbar
	 *
	 * @param array $patientData [FirstName,LastName,BirthDate,ID,MRN,identifiers]
	 * @return void
	 */
	public function renderNavBar($patientData)
	{
		global $lang;
		if(empty($patientData))
			throw new \Exception("Error: no patient data has been found", 1);

		$ehr_user = FhirLauncher::getSessionData('ehr_user');

		// set template variables
		$variables = array(
			'lang' => $lang,
			'patientFirstName' => @$patientData['FirstName'],
			'patientLastName' => @$patientData['LastName'],
			'patientBirthDate' => @$patientData['BirthDate'],
			'patientID' => @$patientData['ID'],
			'patientMrn' => @$patientData['MRN'],
			'app_path_webroot' => APP_PATH_WEBROOT,
			'app_path_images' => APP_PATH_IMAGES,
			'ehr_user' => $ehr_user,
			'user' => $_SESSION['username'],
		);
		if (defined("PROJECT_ID")) $variables['app_title'] = $GLOBALS['app_title'];
		print \Renderer::run('ehr.navbar', $variables);
    }
	
	// Add/remove project from Registered Project list, then redirect back to prev page.
	private function checkAddRemoveProject()
	{
		
		$user_id = self::getUserID();
		$redirect_url = self::getFhirRedirectUrl();
		if (isset($_GET['fhirPatient']) && $patient_id = urldecode($_GET['fhirPatient'])) $redirect_url .= "?fhirPatient={$patient_id}";
		// Add
		if (isset($_GET['addProject']) && is_numeric($_GET['addProject'])) {
			if(empty($user_id)) loginFunction(); // user must be logged in to proceed
			$sql = "insert into redcap_ehr_user_projects (project_id, redcap_userid) values ('".db_escape($_GET['addProject'])."', ".$user_id.")";
			if (db_query($sql)) \HttpClient::redirect($redirect_url);
		}		
		// Remove
		elseif (isset($_GET['removeProject']) && is_numeric($_GET['removeProject'])) {
			if(empty($user_id)) loginFunction(); // user must be logged in to proceed
			$sql = "delete from redcap_ehr_user_projects where project_id = '".db_escape($_GET['removeProject'])."' and redcap_userid = ".$user_id;
			if (db_query($sql)) \HttpClient::redirect($redirect_url);
		}
	}
	
	// Query table to determine if REDCap username has been allowlisted for DDP on FHIR
	public function isDdpUserAllowlistedForFhir($username)
	{		
		$sql = "select 1 from redcap_ehr_user_map m, redcap_user_information i
				where i.ui_id = m.redcap_userid and i.username = '".db_escape($username)."'";
		$q = db_query($sql);
		return (db_num_rows($q) > 0);
	}
	
	/**
	 * Query table to determine if REDCap username has been allowlisted for Data Mart project creation rights.
	 * Super users are allowed by default.
	 * 
	 */
	public static function isDdpUserAllowlistedForDataMart($username)
	{		
		$sql = "SELECT 1 FROM redcap_user_information WHERE username = '".db_escape($username)."'
				AND (super_user = 1 OR fhir_data_mart_create_project = 1)";
		$q = db_query($sql);
		return (db_num_rows($q) > 0);
	}
	
	// Obtain the FHIR redirect URL for this external module (assumes that page=index is the main launching page)
	public static function getFhirRedirectUrl()
	{
		return APP_PATH_WEBROOT_FULL . self::$fhirRedirectPage;
	}
	
	// Obtain the FHIR service endpoint base URL
	public static function getFhirEndpointBaseUrl()
	{
		global $fhir_endpoint_base_url;
		// If we are launching and have launch and iss params, then override with iss param
		if (isset($_GET['launch']) && isset($_GET['iss']))
		{
			$fhirEndpoint = rawurldecode(urldecode($_GET['iss']));
		}
		// Get endpoint from module config. Also, add it to session to keep
		else {
			$fhirEndpoint = $fhir_endpoint_base_url;
		}
		// Ensure the endpoint ends with a slash "/"	
		if (substr($fhirEndpoint, -1) != "/") $fhirEndpoint .= "/";
		// Return the endpoint
		return $fhirEndpoint;
	}
	
	// Initialization check to ensure we have all we need
	private function initCheck()
	{
		$errors = array();
		if (empty($GLOBALS['fhir_client_id'])) {
			$errors[] = "Missing the FHIR client_id! Please add value to module configuration.";
		}
		if (empty($GLOBALS['fhir_endpoint_base_url'])) {
			$errors[] = "Missing the FHIR endpoint base URL! Please add value to module configuration.";
		}
		if (!empty($errors)) {
			throw new \Exception("<br>- ".implode("<br>- ", $errors));
		}	
	}

	/**
	 * get FhirServices
	 *
	 * @return FhirServices
	 */
	public static function getFhirServices($endpoint=null)
	{
			global $fhir_endpoint_base_url, $fhir_client_id, $fhir_client_secret;
			$endpoint = $endpoint ?: $fhir_endpoint_base_url;
			return new FhirServices($endpoint, $fhir_client_id, $fhir_client_secret);
	}


	/**
	 * check if a project has EHR servvices enabled
	 *
	 * @param integer $project_id
	 * @return boolean
	 */
	public static function isFhirEnabledInProject($project_id)
	{
		$project = new \Project($project_id);
		$realtime_webservice_enabled = $project->project['realtime_webservice_enabled'];
		$realtime_webservice_type = $project->project['realtime_webservice_type'];
		$datamart_enabled = $project->project['datamart_enabled'];
		return ( $datamart_enabled==true || ($realtime_webservice_enabled==true && $realtime_webservice_type=='FHIR') );
	}

	/**
	 * render the menu for the FHIR tools
	 *
	 * @param string $menu_id
	 * @param boolean $collapsed 
	 * @return string
	 */
	public static function renderFhirLaunchModal()
	{
		global $lang, $fhir_standalone_authentication_flow, $fhir_source_system_custom_name;
		$autorization_flow = $fhir_standalone_authentication_flow;
		// exit if we are in client credentials authentication mode or if standalone launch is not enabled
		if( $autorization_flow != self::AUTHENTICATION_FLOW_STANDARD) return;
		
		// get token 
		$user_id = FhirEhr::getUserID();
		$tokenManager = new FhirTokenManager($user_id);
		$token = $tokenManager->getToken();
		$token_found = $token instanceof FhirToken;
		$token_valid =  $token_found and $token->isValid();

		// exit if the token is valid
		if($token_valid) return;

		$data = array(
			'lang' => $lang,
			'autorization_flow' => $autorization_flow,
			'ehr_system_name' => strip_tags($fhir_source_system_custom_name),
			'app_path_webroot' => APP_PATH_WEBROOT,
		);
		$modal = \Renderer::run('ehr.launch_modal', $data);
		return $modal;
	}

	
	// Obtain an array (pid=>array(attributes)) of the current user's registered projects
	private function setRegisteredProjects($mrn, $user_id)
	{
		// Query projects
		// $sql = "select p.project_id, if (u.role_id is null, u.record_create, (select ur.record_create from redcap_user_roles ur 
					// where ur.role_id = u.role_id and ur.project_id = p.project_id)) as record_create, 
				// p.auto_inc_set as record_auto_numbering, p.app_title, if (x.project_id is null, 0, 1) as has_mrn_field_type,
				// if (p.realtime_webservice_enabled = '1' and p.realtime_webservice_type = 'FHIR', 1, 0) as ddp_enabled, 				
				// if (p.realtime_webservice_enabled = '1' and p.realtime_webservice_type = 'FHIR', d2.record, d.record) as record,
				// if (p.realtime_webservice_enabled = '1' and p.realtime_webservice_type = 'FHIR', r.item_count, null) as ddp_items
				// from redcap_user_rights u, redcap_user_information i, redcap_ehr_user_projects e, redcap_projects p
				// left join (select m.project_id, m.field_name from redcap_metadata m, redcap_validation_types v, redcap_user_rights u2, redcap_user_information i2 
					// where v.data_type = 'mrn' and m.element_validation_type = v.validation_name and u2.project_id = m.project_id 
					// and u2.username = i2.username and i2.ui_id = '".db_escape($this->ehrUIID)."') x on p.project_id = x.project_id
				// left join redcap_data d on d.project_id = p.project_id and d.field_name = x.field_name 
					// and d.value = '".db_escape($_SESSION['ehr-fhir']['patientInfo'][$this->fhirPatient]['fhirPatientMRN'])."'
				// left join redcap_ddp_mapping dm on dm.project_id = p.project_id and dm.is_record_identifier = 1
				// left join redcap_data d2 on d2.project_id = p.project_id and d2.field_name = dm.field_name 
					// and d2.value = '".db_escape($_SESSION['ehr-fhir']['patientInfo'][$this->fhirPatient]['fhirPatientMRN'])."'				
				// left join redcap_ddp_records r on r.project_id = p.project_id and r.record = d2.record
				// where e.redcap_userid = i.ui_id and p.project_id = e.project_id and p.date_deleted is null 
					// and u.project_id = p.project_id and u.username = i.username and i.ui_id = '".db_escape($this->ehrUIID)."'
					// and p.status in (0, 1)
				// order by p.project_id";
		$sql = "select p.project_id, if (u.role_id is null, u.record_create, (select ur.record_create from redcap_user_roles ur 
					where ur.role_id = u.role_id and ur.project_id = p.project_id)) as record_create, 
				p.auto_inc_set as record_auto_numbering, p.app_title,
				if (p.realtime_webservice_enabled = '1' and p.realtime_webservice_type = 'FHIR', 1, 0) as ddp_enabled, 				
				if (p.realtime_webservice_enabled = '1' and p.realtime_webservice_type = 'FHIR', d2.record, null) as record,
				if (p.realtime_webservice_enabled = '1' and p.realtime_webservice_type = 'FHIR', r.item_count, null) as ddp_items
				from redcap_user_rights u, redcap_user_information i, redcap_ehr_user_projects e, redcap_projects p
				left join redcap_ddp_mapping dm on dm.project_id = p.project_id and dm.is_record_identifier = 1
				left join redcap_data d2 on d2.project_id = p.project_id and d2.field_name = dm.field_name 
					and d2.value = '".db_escape($mrn)."'				
				left join redcap_ddp_records r on r.project_id = p.project_id and r.record = d2.record
				where e.redcap_userid = i.ui_id and p.project_id = e.project_id and p.date_deleted is null and p.completed_time is null 
					and u.project_id = p.project_id and u.username = i.username and i.ui_id = '".db_escape($user_id)."'
					and p.status in (0, 1)
				order by p.project_id";
		$q = db_query($sql);
		$projects = array();
		while ($row = db_fetch_assoc($q)) {
			$pid = $row['project_id'];
			unset($row['project_id']);
			$row['app_title'] = strip_tags($row['app_title']);
			// Add to array
			$projects[$pid] = $row;
		}
		return $projects;
	}
	
	// Obtain an array (pid=>title) of the current user's UNregistered projects.
	// Separate viable and non-viable projects in sub-arrays
	private function setUnegisteredProjects($user_id)
	{
		global $lang;
		
		// $sql = "select p.project_id, p.app_title,
				// if ((x.project_id is not null or (p.realtime_webservice_enabled = '1' and p.realtime_webservice_type = 'FHIR')), 1, 0) as viable
				// from (redcap_user_rights u, redcap_user_information i, redcap_projects p)
				// left join redcap_ehr_user_projects e on e.project_id = p.project_id and e.redcap_userid = i.ui_id
				// left join (select m.project_id, m.field_name from redcap_metadata m, redcap_validation_types v, redcap_user_rights u2, redcap_user_information i2 
					// where v.data_type = 'mrn' and m.element_validation_type = v.validation_name and u2.project_id = m.project_id 
					// and u2.username = i2.username and i2.ui_id = '".db_escape($this->ehrUIID)."') x on p.project_id = x.project_id
				// where p.date_deleted is null and u.project_id = p.project_id and u.username = i.username 
					// and e.redcap_userid is null and i.ui_id = '".db_escape($this->ehrUIID)."' and p.status in (0, 1) 
				// order by if ((x.project_id is not null or (p.realtime_webservice_enabled = '1' and p.realtime_webservice_type = 'FHIR')), 1, 0) desc, p.project_id";
		$sql = "select p.project_id, p.app_title,
				if ((p.realtime_webservice_enabled = '1' and p.realtime_webservice_type = 'FHIR'), 1, 0) as viable
				from (redcap_user_rights u, redcap_user_information i, redcap_projects p)
				left join redcap_ehr_user_projects e on e.project_id = p.project_id and e.redcap_userid = i.ui_id
				where p.date_deleted is null and u.project_id = p.project_id and u.username = i.username 
					and e.redcap_userid is null and i.ui_id = '".db_escape($user_id)."' and p.status in (0, 1) 
				order by if ((p.realtime_webservice_enabled = '1' and p.realtime_webservice_type = 'FHIR'), 1, 0) desc, p.project_id";
		$q = db_query($sql);
		$projects = array();
		while ($row = db_fetch_assoc($q)) {
			$row['app_title'] = strip_tags($row['app_title']);
			$viableText = $row['viable'] ? $lang['data_entry_395'] : $lang['data_entry_396'];
			$projects[$viableText][$row['project_id']] = strip_tags($row['app_title']);
		}
		return $projects;
	}

	/**
	 * check if clinical data interoperability services are enabled
	 * at the system-level in REDCap
	 *
	 * @return boolean
	 */
	public static function isCdisEnabledInSystem()
	{
		global $realtime_webservice_global_enabled, $fhir_ddp_enabled, $fhir_data_mart_create_project;
		
		$cdp_custom_enabled = boolval($realtime_webservice_global_enabled);
		$cdp_enabled = boolval($fhir_ddp_enabled);
		$data_mart_enabled = boolval($fhir_data_mart_create_project);
		return $cdp_custom_enabled || $cdp_enabled || $data_mart_enabled;
	}
	
}
