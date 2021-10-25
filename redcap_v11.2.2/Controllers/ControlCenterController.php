<?php

use Vanderbilt\REDCap\Classes\Fhir\Endpoints\AbstractEndpoint;
use Vanderbilt\REDCap\Classes\Fhir\Endpoints\FhirRequest;
use Vanderbilt\REDCap\Classes\Fhir\FhirCategory;
use Vanderbilt\REDCap\Classes\Fhir\FhirClient;
use Vanderbilt\REDCap\Classes\Fhir\FhirEhr;
use Vanderbilt\REDCap\Classes\Fhir\FhirServices;
use Vanderbilt\REDCap\Classes\Fhir\Resources\Shared\Bundle;
use Vanderbilt\REDCap\Classes\Fhir\Resources\Shared\Patient;
use Vanderbilt\REDCap\Classes\Fhir\TokenManager\FhirTokenManager;

class ControlCenterController extends Controller
{
	// Perform the One-Click Upgrade
	public function oneClickUpgrade()
	{
		if (!SUPER_USER) exit('ERROR'); // Super users only
		Upgrade::performOneClickUpgrade();
	}
	
	// Execute the upgrade SQL script to complete an upgrade
	public function executeUpgradeSQL()
	{
		if (!SUPER_USER) exit('ERROR'); // Super users only
		print Upgrade::executeUpgradeSQL($_POST['version']);
	}
	
	// Execute the upgrade SQL script to complete an upgrade
	public function autoFixTables()
	{
		// Super users only
		print ((SUPER_USER && Upgrade::autoFixTables()) ? '1' : '0');
	}
	
	// Hide the Easy Upgrade box on the main Control Center page
	public function hideEasyUpgrade()
	{
		// Super users only
		print ((SUPER_USER && Upgrade::hideEasyUpgrade($_POST['hide'])) ? '1' : '0');
	}
	
	/**
	 * get patient string identifiers from a patient using a social security number
	 * @see https://www.hl7.org/fhir/identifier-registry.html
	 *
	 * @return void
	 */
	public function getFhirStringIdentifiers()
	{
		/**
		 * @param FhirClient $fhirClient
		 * @param string $systemIdentifier
		 * @param string $ssn
		 * @return Patient
		 */
		$getPatient = function($fhirClient, $systemIdentifier, $ssn) {
			$endpointFactory = $fhirClient->getFhirVersionManager()->getEndpointFactory();
			$endpoint = $endpointFactory->makeEndpoint(FhirCategory::DEMOGRAPHICS);
			$request = $endpoint->getSearchRequest(['identifier'=> "{$systemIdentifier}|{$ssn}"]);
			/** @var Bundle $bundle */
			$bundle = $fhirClient->getResource($request);
			$entries = $bundle->getEntries();
			/** @var Patient $patient */
			$patient = current($entries);
			return $patient;
		};

		try {
			global $userid;
			$user_id = User::getUIIDByUsername($userid);
			$ssn = trim($_GET['ssn']);
			preg_match('/[^\d\s-]/',$ssn, $not_allowed_matches); // only numbers, dashes, and spaces are allowed
			// check if SSN is empty or contains characters not allowed
			if(empty($ssn) || !empty($not_allowed_matches)) throw new Exception("Error: A valid SSN must be provided", 400);

			// extract numbers from the ssn string
			preg_match_all('/\d+/', $ssn, $matches);
			$ssn_numbers = implode('', $matches[0]);

			$tokenManager = new FhirTokenManager($user_id);
			$fhirClient = new FhirClient($project_id=null, $tokenManager);
			$systemIdentifiers = [
				'OID' => '2.16.840.1.113883.4.1', // this works for Epic
				'URI' => 'http://hl7.org/fhir/sid/us-ssn', // this works for Smart Health IT
			];
			foreach ($systemIdentifiers as $systemIdentifier) {
				$patient = $getPatient($fhirClient, $systemIdentifier, $ssn);
				if($patient) break;
			}

			if(empty($patient)) throw new Exception("No patient found for the provided SSN ({$ssn_numbers})", 404);
			$string_identifiers = $patient->getIdentifiers();
			// $string_identifiers = $patient->getIdentifiers();

			$response = array(
				'ssn' => $ssn_numbers,
				// 'patient' => $data->entry[0]->resource,
				'string_identifiers' => $string_identifiers,
				'success' => true,
			);

			HttpClient::printJSON($response);
		} catch (\Exception $e) {
			$response = array('message' => $e->getMessage());
			HttpClient::printJSON($response, $e->getCode());
		}
	}

	public static function saveAdminPriv()
	{
		if (!ADMIN_RIGHTS) exit('0');
		print User::saveAdminPriv($_POST['userid'], $_POST['attr'], $_POST['value']) ? '1' : '0';
	}

	public static function saveNewAdminPriv()
	{
		if (!ADMIN_RIGHTS) exit('0');
		$success = false;
		foreach (explode(",", $_POST['attrs']) as $attr) {
			if (User::saveAdminPriv($_POST['userid'], $attr, '1')) {
				$success = true;
			}
		}
		print $success ? '1' : '0';
	}

	public static function getUserIdByUsername()
	{
		if (!ADMIN_RIGHTS) exit('0');
		$this_ui_id = User::getUIIDByUsername($_POST['username']);
		print is_numeric($this_ui_id) ? $this_ui_id : 0;
	}

}