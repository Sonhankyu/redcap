<?php namespace Vanderbilt\REDCap\Classes\Fhir\ClinicalDataPull;

use Vanderbilt\REDCap\Classes\Fhir\Resources\AbstractResource;
use Vanderbilt\REDCap\Classes\Fhir\Resources\DSTU2\AllergyIntolerance as AllergyIntolerance_DSTU2;
use Vanderbilt\REDCap\Classes\Fhir\Resources\R4\AllergyIntolerance as AllergyIntolerance_R4;
use Vanderbilt\REDCap\Classes\Fhir\Resources\DSTU2\MedicationOrder;
use Vanderbilt\REDCap\Classes\Fhir\Resources\R4\AdverseEvent;
use Vanderbilt\REDCap\Classes\Fhir\Resources\R4\Immunization;
use Vanderbilt\REDCap\Classes\Fhir\Resources\R4\MedicationRequest;
use Vanderbilt\REDCap\Classes\Fhir\Resources\ResourceVisitorInterface;
use Vanderbilt\REDCap\Classes\Fhir\Resources\Shared\CodingSystem;
use Vanderbilt\REDCap\Classes\Fhir\Resources\Shared\Condition;
use Vanderbilt\REDCap\Classes\Fhir\Resources\Shared\Observation;
use Vanderbilt\REDCap\Classes\Fhir\Resources\Shared\Patient;

/**
 * resource visitor that uses
 * the mapping format of REDCap CDIS projects:
 * [fields, dateMin, dateMax]
 */
class ResourceVisitor implements ResourceVisitorInterface
{

  /**
   * list of mapped fields
   *
   * @var array
   */
  private $fields;
  /**
   * date range specified for these fields
   *
   * @var array
   */
  private $dateRange;


  /**
   * store modified data
   *
   * @var array
   */
  private $data = [];

  /**
   * text that separates multiple entries
   * in some categories (e.g. Medications, Conditions, etc...)
   *
   * @var string
   */
  private static $new_line_separator = "\r\n-----\r\n";

  /**
   *
   * @param array $fields
   * @param string $dateMin
   * @param string $dateMax
   */
  function __construct($fields, $dateMin, $dateMax)
  {
    $this->fields = $fields;
    $this->dateRange = [];
    if($dateMin) $this->dateRange[] = $dateMin;
    if($dateMax) $this->dateRange[] = $dateMax;
  }

  public function getData()
  {
    return $this->data;
  }

  public function addData($field, $value, $timestamp=null)
  {
     $data = [
      'field' => $field,
      'value' => $value,
      'timestamp' => $timestamp,
    ];
    $this->data[] = $data;
  }

  /**
   * update an entry at the specified index
   *
   * @param int $index
   * @param array $data
   * @return void
   */
  public function updateData($index, $data)
  {
    $previousEntry = @$this->data[$index];
    if(!$previousEntry) return;
    // make sure to get just the suitable data
    $updatedData = [
      'field' => @$data['field'],
      'value' => @$data['value'],
      'timestamp' => @$data['timestamp'],
    ];
    $this->data[$index] = $updatedData;
  }

  /**
   * get the index of the first corresponding
   * field in the data array.
   * this is an helper method used mostly 
   * for resources not meant to be repeatable (e.g. Medications, Conditions, Immunizations...)
   *
   * @param string $field
   * @return int|false
   */
  private function findEntry($field) {
      $fields = array_column($this->data, 'field');
      $index = array_search($field, $fields);
      return $index;
  }

  /**
   * squash text values for a resource that is not
   * meant to be repeatable (e.g. Medications, Conditions, Immunizations...)
   *
   * @param int $index position of the existing entry
   * @param string $text text to append to the existing entry
   * @return void
   */
  private function appendText($index, $text) {
    // update existing data if found (concat text)
    $previousEntry = @$this->data[$index];
    if(!$previousEntry) return;
    $previousValue = @$previousEntry['value'] ?: '';
    $previousEntry['value'] = $previousValue.self::$new_line_separator.$text;
    $this->updateData($index, $previousEntry);
  }

  /**
   * adjust the data for the resource
   * always return an array of entries to
   * normalize the behaviour of observations where
   * we want to maitain one LOINC code per row
   * 
   * @param AbstractResource $resource
   * @return array
   */
  function visit($resource)
  {
    $class = get_class($resource);
    switch ($class) {
      case AdverseEvent::class:
        $this->visitAdverseEvent($resource);
        break;
      case Patient::class:
        $this->visitPatient($resource);
        break;
      case Observation::class:
        $this->visitObservation($resource);
        break;
      /* case Encounter::class:
        $this->visitEncounter($resource);
        break; */
      case AllergyIntolerance_DSTU2::class:
      case AllergyIntolerance_R4::class:
        $this->visitAllergy($resource);
        break;
      case Immunization::class:
        $this->visitImmunization($resource);
        break;
      case Condition::class:
        $this->visitCondition($resource);
        break;
      case MedicationOrder::class:
      case MedicationRequest::class:
        $this->visitMedication($resource);
        break;
      default:
        [$resource->getData()];
        break;
    }
  }

  /**
   * @param Patient $resource
   * @return void
   */
  public function visitPatient($resource)
  {
    // extract only mapped data
    foreach ($this->fields as $key) {
      $callable = $resource->getCallable($key);
      $value = $callable();
      $this->addData($key, $value);
    }
  }

  /**
   * filter based on mapped LOINC codes
   *
   * @param Observation $resource
   * @return void
   */
  public function  visitObservation($resource)
  {
    $observations = $resource->splitComponents(); // observation must be treated as a list because of 'components'
    
    foreach ($observations as $observation) {
      $code = $observation->getCode();
      $codingList = $code->getCoding();
      $text = $code->getText();
      $codes = array_column($codingList, 'code');
      $systems = array_column($codingList, 'system');
      $matchingCodes = array_intersect($codes, $this->fields);
      // check all codings for matching LOINC codes
      array_walk($codingList, function($coding) use($observation, $text) {
        $match = preg_match(CodingSystem::LOINC, @$coding['system']);
        if(!$match) return false;
        $code = @$coding['code'];
        $found = in_array($code, $this->fields);
        if(!$found) return false;
        $value = $observation->getValue();
        $timestamp = $observation->getNormalizedTimestamp();
        // $label =  @$coding['display'] ?: $text;
        $this->addData($code, $value, $timestamp);
      });
    }
  }

  /**
   * get only medications with mapped status
   *
   * @param MedicationOrder|MedicationRequest $resource
   * @return void
   */
  public function visitMedication($resource)
  {
    // helper function to extract text from the resource data
    $getText = function() use($resource) {
      $text = '';
      $medicationReference = $resource->getMedicationReference();
      $display = $medicationReference ?: $resource->getText();
      if($display) $text .= $display;
      if($dosage_timing = $resource->getDosageInstructionTiming()) $text .= ", {$dosage_timing}";
      if($status = $resource->getStatus()) $text .= " - {$status}";
      if($timestamp = $resource->getNormalizedTimestamp()) $text .= " - {$timestamp}";
      return $text;
    };
    $statusList = preg_replace("/-medications-list$/",'', $this->fields);
    $status = $resource->getStatus();
    if(!in_array($status, $statusList)) return; // exit if the status was not mapped
    $key = "{$status}-medications-list"; // key as it is set in the metadata file
    $text = $getText();
    $previousEntryIndex = $this->findEntry($key);
    if($previousEntryIndex===false) $this->addData($key, $text);
    else $this->appendText($previousEntryIndex, $text);
  }

  /**
   *
   * @param Condition $resource
   * @return void
   */
  public function visitCondition($resource)
  {
    // helper function to extract text from the resource data
    $getText = function() use($resource) {
      $label = $resource->getLabel() ?: ''; // default text
      $printCodingSystem = function($codingSystem, $system=null) use($label){
        $system = $system ?: @$codingSystem['system'];
        $display =  @$codingSystem['display'] ?: $label;
        return sprintf('%s (%s %s)', $display, $system, @$codingSystem['code']);
      };
      $codingSystems = [];
      if($icd9Cm = $resource->getIcd9()) $codingSystems[] = $printCodingSystem($icd9Cm, CodingSystem::ICD_9_CM_NAME);
      if($icd10Cm = $resource->getIcd10()) $codingSystems[] = $printCodingSystem($icd10Cm, CodingSystem::ICD_10_CM_NAME);
      if($snomedCt = $resource->getSnomed()) $codingSystems[] = $printCodingSystem($snomedCt, CodingSystem::SNOMED_CT_NAME);

      $text = '';
      // if($label = @$data['label']) $text .= $label;
      $text .= implode(', ', $codingSystems);
      $status = $resource->getClinicalStatus() ?: ($resource->getClinicalStatusCode() ?: '');
      if($status) $text .= " - {$status}";
      if($timestamp = $resource->getDateRecorded()) $text .= " - {$timestamp}";
      return $text;
    };
    
    $key = "problem-list"; // key as it is set in the metadata file
    $text = $getText();
    $previousEntryIndex = $this->findEntry($key);
    if($previousEntryIndex===false) $this->addData($key, $text);
    else $this->appendText($previousEntryIndex, $text);
  }

  /**
   *
   * @param AllergyIntolerance_DSTU2|AllergyIntolerance_R4 $resource
   * @return void
   */
  public function visitAllergy($resource)
  {
    // helper function to extract text from the resource data
    $getText = function() use($resource) {
      $printCodingSystem = function($codingSystem, $system=null) {
        $system = $system ?: @$codingSystem['system'];
        return sprintf('%s (%s %s)', @$codingSystem['display'], $system, @$codingSystem['code']);
      };
      $codingSystems = [];
      if($rxnorm = $resource->getRxnorm()) $codingSystems[] = $printCodingSystem($rxnorm, CodingSystem::RxNorm_NAME);
      if($ndfRt = $resource->getNdfRt()) $codingSystems[] = $printCodingSystem($ndfRt, CodingSystem::NDF_RT_NAME);
      if($fdaUnii = $resource->getFdaUnii()) $codingSystems[] = $printCodingSystem($fdaUnii, CodingSystem::FDA_UNII_NAME);
      if($snomed = $resource->getSnomed()) $codingSystems[] = $printCodingSystem($snomed, CodingSystem::SNOMED_CT_NAME);

      $text = '';
      // if($label = @$data['label']) $text .= $label;
      $text .= implode(', ', $codingSystems);
      $substanceText = $resource->getText();
      if(empty($codingSystems) && $substanceText) $text .= $substanceText;
      $timestamp = $resource->getNormalizedTimestamp();
      if($timestamp) $text .= " - {$timestamp}";
      return $text;
    };
    // value:"LATEX (RxNorm 1314891), LATEX (FDA UNII 2LQ0UUW8IN) - 2024-07-12"
    $key = "allergy-list"; // key as it is set in the metadata file
    $text = $getText();
    $previousEntryIndex = $this->findEntry($key);
    if($previousEntryIndex===false) $this->addData($key, $text);
    else $this->appendText($previousEntryIndex, $text);
  }

  /**
   *
   * @param AdverseEvent $resource
   * @return void
   */
  public function visitAdverseEvent($resource)
  {
    // helper function to extract text from the resource data
    $getText = function() use($resource) {
      $text = '';
      if($event = $resource->getEvent()) $text .= $event;
      if($studies = $resource->getStudies()) $text .= sprintf(' (%s)', $studies);
      if($seriousness = $resource->getSeriousness()) $text .= sprintf(' - %s', $seriousness);
      if($outcome = $resource->getOutcome()) $text .= sprintf(', %s', $outcome);
      if($timestamp = $resource->getNormalizedTimestamp()) $text .= sprintf(' - %s', $timestamp);
      return $text;
    };
    $key = "adverse-events-list"; // key as it is set in the metadata file
    $text = $getText();
    $previousEntryIndex = $this->findEntry($key);
    if($previousEntryIndex===false) $this->addData($key, $text);
    else $this->appendText($previousEntryIndex, $text);
  }

  /**
   *
   * @param Immunization $resource
   * @return void
   */
  public function visitImmunization($resource)
  {
    // helper function to extract text from the resource data
    $getText = function() use($resource) {
      $text = '';
      if($label = $resource->getText()) $text .= $label;
      if($cdv_code = $resource->getCvxCode()) $text .= sprintf(' (CVX %s)', $cdv_code);
      if($status = $resource->getStatus()) $text .= sprintf(' - %s', $status);
      if($timestamp = $resource->getNormalizedTimestamp()) $text .= sprintf(' - %s', $timestamp);
      return $text;
    };
    $key = "immunizations-list"; // key as it is set in the metadata file
    $text = $getText();
    $previousEntryIndex = $this->findEntry($key);
    if($previousEntryIndex===false) $this->addData($key, $text);
    else $this->appendText($previousEntryIndex, $text);
  }  

}