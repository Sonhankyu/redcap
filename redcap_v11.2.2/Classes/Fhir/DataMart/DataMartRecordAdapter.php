<?php
namespace Vanderbilt\REDCap\Classes\Fhir\DataMart;

use SplObserver;
use Vanderbilt\REDCap\Classes\Fhir\DataMart\Forms\AdverseEvents;
use Vanderbilt\REDCap\Classes\Fhir\DataMart\Forms\Allergies;
use Vanderbilt\REDCap\Classes\Fhir\DataMart\Forms\CoreCharacteristics;
use Vanderbilt\REDCap\Classes\Fhir\DataMart\Forms\Demography;
use Vanderbilt\REDCap\Classes\Fhir\DataMart\Forms\Encounters;
use Vanderbilt\REDCap\Classes\Fhir\DataMart\Forms\Form;
use Vanderbilt\REDCap\Classes\Fhir\DataMart\Forms\Immunizations;
use Vanderbilt\REDCap\Classes\Fhir\DataMart\Forms\Labs;
use Vanderbilt\REDCap\Classes\Fhir\DataMart\Forms\Medications;
use Vanderbilt\REDCap\Classes\Fhir\DataMart\Forms\ProblemList;
use Vanderbilt\REDCap\Classes\Fhir\DataMart\Forms\VitalSigns;
use Vanderbilt\REDCap\Classes\Fhir\FhirClient;
use Vanderbilt\REDCap\Classes\Fhir\DataMart\ResourceVisitor;
use Vanderbilt\REDCap\Classes\Fhir\Utility\InstanceSeeker;

/**
 * Adapter to save data coming from FHIR endpoints into a Data Mart record.
 * this object will listen for notification from the FHIR client
 * to populate it's data array (grouped by category)
 */
class DataMartRecordAdapter implements SplObserver
{

	/**
	 * collect stats of fetched data
	 *
	 * @var array
	 */
	private $stats = [];

	/**
	 *
	 * @var string
	 */
	private $mrn;

	/**
	 *
	 * @var DataMartRevision
	 */
	private $revision;

	/**
	 *
	 * @var int
	 */
	private $project_id;

	/**
	 *
	 * @var Project
	 */
	private $project;

	/**
	 * contains all data for a specific record
	 * grouped by category
	 *
	 * @var array
	 */
	private $data = [];
	/**
	 * list of errors
	 *
	 * @var array
	 */
	private $errors = [];

	/**
	 * Create an instance of the adapter
	 *
	 * @param string $mrn
	 * @param \DataMartRevision $revision
	 */
	public function __construct($mrn, $revision)
	{
		$this->mrn = $mrn;
		$this->revision = $revision;
		$this->project_id = $this->revision->project_id;
		$this->project = new \Project($this->project_id);
		// cache a list of fields in the current project
	}

	/**
	 * react to notifications (from the FHIR client)
	 *
	 * @param SplSubject $subject
	 * @param string $event
	 * @param mixed $data
	 * @return void
	 */
	public function update($subject, string $event = null, $data = null)
	{
		if(!($subject instanceof FhirClient)) return;
		switch ($event) {
			case FhirClient::NOTIFICATION_ENTRIES_RECEIVED:
				$category = @$data['category'];
				$entries = @$data['entries'];
				$mapping = @$data['mapping'];
				$this->addData($category, $entries, $mapping);
				break;
			case FhirClient::NOTIFICATION_ERROR:
				$this->addError($data);
				break;
			default:
				# code...
				break;
		}
	}

	/**
	 * apply the resource visitor to the received data
	 * and store it in its group
	 *
	 * @param string $category
	 * @param array $entries
	 * @param array $mapping [[field, timestamp_min, timestamp_max]]
	 * @return void
	 */
	public function addData($category, $entries, $mapping)
	{
		/**
		 * extract data from each resource
		 * and make necessary transformations if needed
		 */
		$mapEntries = function($entries, $mapping) {
			$resourceVisitor = new ResourceVisitor($mapping['fields'], $mapping['dateMin'], $mapping['dateMax']);
			$data = [];
			foreach ($entries as $entry) {
					$entryData = $entry->accept($resourceVisitor);
					$data = array_merge($data, $entryData);
			}
			return $data;
		};

		$mappedEntries = $mapEntries($entries, $mapping);
		$this->data[$category] = $mappedEntries;
	}

	public function addError($data)
	{
		$this->errors[] = $data;
	}

	public function getErrors()
	{
		return $this->errors;
	}

	public function hasErrors()
	{
		return count($this->errors)>0;
	}


	private function getFormforCategory($fhirCategory)
	{
		$project = $this->project;
		switch ($fhirCategory) {
			case 'Laboratory':
				$form = new Labs($project);
				break;
			case 'Vital Signs':
				$form = new VitalSigns($project);
				break;
			case 'Allergy Intolerance':
				$form = new Allergies($project);
				break;
			case 'Medications':
				$form = new Medications($project);
				break;
			case 'Condition':
				$form = new ProblemList($project);
				break;
			case 'Demographics':
				$form = new Demography($project);
				break;
			case 'Encounter':
				$form = new Encounters($project);
				break;
			case 'Immunization':
				$form = new Immunizations($project);
				break;
			case 'Core Characteristics':
				$form = new CoreCharacteristics($project);
				break;
			case 'Adverse Event':
				$form = new AdverseEvents($project);
				break;
			default:
				$form = null;
				break;
		}
		return $form;
	}

	/**
	 * update the stats with the amount of
	 * data fetched and saved per category
	 *
	 * @param string $category
	 * @param array $data
	 * @param Boolean $repeating
	 * @return void
	 */
	function updateStats($category, $data, $repeating)
	{
		if($repeating) {
			$this->stats[$category] = intval(@$this->stats[$category])+1;
		}else {
			$this->stats[$category] = intval(@$this->stats[$category])+count($data);
		}
	}

	/**
	 * return collected stats
	 *
	 * @return array
	 */
	function getStats() {
		return $this->stats;
	}

	public function getRecord()
	{
		$getNextInstanceInRecord = function($record, $formName) {
			if(empty($record)) return 1;
			$recordData = reset($record) ?: []; //extract what is inside recordId
			$repeatInstancesList = @$recordData['repeat_instances'] ?: [];
			$repeatInstancesData = reset($repeatInstancesList) ?: []; // extract what is inside event_id
			$repeatInstanceData = @$repeatInstancesData[$formName] ?: [];
			$lastInstance = end(array_keys($repeatInstanceData));
			return intval($lastInstance)+1;
		};
		
		$mrn = $this->mrn;
		$groupedData = $this->data;
		// Instantiate project
		$project = $this->project;
		$project_id = $project->project_id;
		$event_id = $project->firstEventId;
		$recordId = InstanceSeeker::getRecordID($project_id, $event_id, 'mrn', $mrn);
		if(!$recordId) {
			throw new \Exception("Error: the specified MRN is not in the project", 1);
		}
		
		$recordSeed = [];
		// get the event ID. Will be used to save data in the record structure
		foreach ($groupedData as $category => $entries) {
			$form = $this->getFormforCategory($category);
			if(!$form instanceof Form) continue;
			$formName = $form->getFormName();
			$instanceSeeker = new InstanceSeeker($project, $formName);
			
			foreach ($entries as $entry) {
				$data = $form->mapFhirData($entry);
				if($repeating=$form->isRepeating()) {
					$fullMatch = $instanceSeeker->findMatches($recordId, $data, array_keys($data));
					if($fullMatch) continue;
					$uniquenessFields = $form->getUniquenessFields();
					$matchingInstance = $instance_number = $instanceSeeker->findMatches($recordId, $data, $uniquenessFields);
					if(!$matchingInstance) {
						// choose the next instance number between the database and the recordSeed 
						$db_instance_number = $instanceSeeker->getAutoInstanceNumber($recordId);
						$recordSeedInstance = $getNextInstanceInRecord($recordSeed, $formName);
						$instance_number = max($db_instance_number, $recordSeedInstance);
					}
				}else {
					$differentFields = $instanceSeeker->getNonMatchingFields($recordId, $data);
					// only consider different and non empty values for insertion
					$data = array_filter($data, function($value, $key) use($differentFields) {
						if(empty($value)) return false; // skip empty values
						return in_array($key, $differentFields); // only keep different fields
					}, ARRAY_FILTER_USE_BOTH);
					if(count($data)<1) continue;
					$instance_number = 1;
				}
				
				$completeData = [];
				// add the information to mark the form as "completed" if there is data to save
				if(!empty($data)) $completeData = $form->addCompleteFormData($data);
				// add data to the record seed
				foreach($completeData as $field_name => $value) {
					$recordSeed = $form->reduceRecord($recordId, $event_id, $field_name, $value, $instance_number, $recordSeed);
				}
				// update stats using the data (do not count the {form_name}_complete field)
				$this->updateStats($category, $data, $repeating);
			}

		}
		return $recordSeed;
	}

}