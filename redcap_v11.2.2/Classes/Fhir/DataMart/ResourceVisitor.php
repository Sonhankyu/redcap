<?php namespace Vanderbilt\REDCap\Classes\Fhir\DataMart;

use System;
use Vanderbilt\REDCap\Classes\Fhir\Resources\AbstractResource;
use Vanderbilt\REDCap\Classes\Fhir\Resources\DSTU2\MedicationOrder;
use Vanderbilt\REDCap\Classes\Fhir\Resources\R4\AdverseEvent;
use Vanderbilt\REDCap\Classes\Fhir\Resources\R4\Encounter;
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
   * list of system settings
   *
   * @var array
   */
  private $systemConfigs;

  /**
   * setting to convert timestamps
   * to local timezone (where applicable)
   *
   * @var boolean
   */
  private $convertToLocalTime = false;

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
    $this->systemConfigs = System::getConfigVals();
    $this->convertToLocalTime = boolval(@$this->systemConfigs['fhir_convert_timestamp_from_gmt']);
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
      case Patient::class:
        $data = $this->visitPatient($resource);
        break;
      case Observation::class:
        $data = $this->visitObservation($resource);
        break;
      case Encounter::class:
        $data = $this->visitEncounter($resource);
        break;
      case AdverseEvent::class:
        $data = $this->visitAdverseEvent($resource);
        break;
      /*case Condition::class:
        $data = $this->visitCondition($resource);
        break; */
      case MedicationOrder::class:
      case MedicationRequest::class:
        $data = $this->visitMedication($resource);
        break;
      default:
        $data = [$resource->getData()];
        break;
    }
    return $data;
  }

  /**
   *
   * @param Patient $resource
   * @return array
   */
  public function visitPatient($resource)
  {
    $resourceData = $resource->getData();
    // extract only mapped data
    $filtered = array_intersect_key($resourceData, array_flip($this->fields));
    return [$filtered];
  }

  /**
   *
   * @param Encounter $resource
   * @return array
   */
  public function visitEncounter($resource)
  {
    $data = $resource->getData();
    $data['normalized_period-start'] = $resource->getTimestampStart($this->convertToLocalTime);
    $data['normalized_period-end'] = $resource->getTimestampEnd($this->convertToLocalTime);
    return [$data];
  }

  /**
   *
   * @param AdverseEvent $resource
   * @return array
   */
  public function visitAdverseEvent($resource)
  {
    $data = $resource->getData();
    $data['normalized_timestamp'] = $resource->getTimestamp($this->convertToLocalTime);
    return [$data];
  }

  /**
   * filter based on mapped LOINC codes
   *
   * @param Observation $resource
   * @return array
   */
  public function  visitObservation($resource)
  {
    /**
     * return observation with matching LOINC code.
     * also sets the codings `code` and `display`
     * in the root of the return data object
     * 
     */
    $getData = function($observation) {
      $code = $observation->getCode();
      // since we splitted the observations each one will contain just one coding:
      $coding = current($code->getCoding());
      if(empty($coding)) return;
      $match = preg_match(CodingSystem::LOINC, @$coding['system']);
      if(!$match) return;
      $code = @$coding['code'];
      $found = in_array($code, $this->fields);
      if(!$found) return;
      // $label =  @$coding['display'] ?: $text;
      $data = $observation->getData();
      $data['code-code'] = @$coding['code'];
      $data['code-display'] = @$coding['display'] ?: $code->getText();
      $data['normalized_timestamp'] = $observation->getTimestamp($this->convertToLocalTime);
      return $data;
    };

    $observations = $resource->split();
    $list = [];
    foreach ($observations as $observation) {
      $data = $getData($observation);
      if($data) $list[] = $data;
    }

    return $list;
  } 

  /**
   * get only medications with mapped status
   *
   * @param MedicationOrder|MedicationRequest $resource
   * @return array
   */
  public function visitMedication($resource)
  {
    $data = $resource->getData();
    $statusList = preg_replace("/-medications-list$/",'', $this->fields);
    $status = @$data['status'];
    if(!in_array($status, $statusList)) return;
    $data['display'] = @$data['display'] ?: @$data['text']; // fix empty display
    return [$data];
  }

  /**
   *
   * @param Condition $resource
   * @return void
   */
  public function visitCondition($resource)
  {

  }

  public function visitAllergy($resource)
  {

  }

  public function visitImmunization($resource)
  {

  }

  /**
   * apply a function to an array
   * and return true as soon as
   * the first element is true
   *
   * @param array $array
   * @param callable $fn
   * @return Boolean
   */
  private static function array_any(array $array, callable $fn) {
    foreach ($array as $value) {
        if($fn($value)) {
            return true;
        }
    }
    return false;
  }

  /**
   * search for something in an array
   * using a user specified function.
   * Exit as soon as the first match is found
   *
   * @param array $items
   * @param callable $function
   * @return mixed
   */
  private static function array_find($items, $function) {
    foreach ($items as $item) {
      if (call_user_func($function, $item) === true) return $item;
    }
    return null;
  }

  

}