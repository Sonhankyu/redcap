<?php
namespace Vanderbilt\REDCap\Classes\Fhir\Resources\R4;

use Vanderbilt\REDCap\Classes\Fhir\Resources\AbstractResource;
use Vanderbilt\REDCap\Classes\Fhir\Resources\Shared\CodeableConcept;
use Vanderbilt\REDCap\Classes\Fhir\Resources\Traits\CanNormalizeTimestamp;

class Encounter extends AbstractResource
{

  use CanNormalizeTimestamp;

  const TIMESTAMP_FORMAT = 'Y-m-d H:i';

  /**
   * get the local or GMT timestamp
   * of the start encounter
   * 
   * @param boolean $localTimestamp
   * @return string
   */
  public function getTimestampStart($localTimestamp=false)
  {
    $callable = $this->getTimestampCallable($this->getPeriodStart(), self::TIMESTAMP_FORMAT);
    return $callable($localTimestamp);
  }

  /**
   * get the local or GMT timestamp
   * of the end encounter
   * 
   * @param boolean $localTimestamp
   * @return string
   */
  public function getTimestampEnd($localTimestamp=false)
  {
    $callable = $this->getTimestampCallable($this->getPeriodEnd(), self::TIMESTAMP_FORMAT);
    return $callable($localTimestamp);
  }

  
  public function getType()
  {
    return $this->query()
      ->select('#/type/\d+/text')
      ->results()
      ->single();
  }

  public function getPeriodStart()
  {
    return $this->query()
      ->select('#/period/start')
      ->results()
      ->single();
  }

  public function getPeriodEnd()
  {
    return $this->query()
      ->select('#/period/end')
      ->results()
      ->single();
  }

  public function getLocation()
  {
    return $this->query()
      ->select('#/location/\d+/location/display')
      ->results()
      ->single();
  }

  public function getReasonText()
  {
    return $this->query()
      ->select('#/reasonCode/\d+/text')
      ->results()
      ->single();
  }

  /**
   * create a CodeableConcept from the code
   * portion of the payload
   *
   * @return CodeableConcept
   */
  public function getReasonCode()
  {
    $payload = $this->query()
      ->select('#/reasonCode/\d+')
      ->results()
      ->expand();
    return new CodeableConcept('reasonCode', $payload);
  }

  public function getClass()
  {
    return $this->query()
      ->select('#/class/display')
      ->results()
      ->single();
  }

  public function getStatus()
  {
    return $this->query()
      ->select('#/status')
      ->results()
      ->single();
  }

  /* public function getReasonCodeSnomed()
  {
    return $this->query()
      ->select('#/reasonCode/\d+/coding')
      ->where('system', 'like', CodingSystem::SNOMED_CT)
      ->orWhere('system', 'like', CodingSystem::SNOMED_CT_1)
      ->select('code')
      ->results()
      ->single();
  }
  public function getReasonDisplaySnomed()
  {
    return $this->query()
      ->select('#/reasonCode/\d+/coding')
      ->where('system', 'like', CodingSystem::SNOMED_CT)
      ->orWhere('system', 'like', CodingSystem::SNOMED_CT_1)
      ->select('display')
      ->results()
      ->single();
  } */


  public function getData()
  {
    $data = [
      'type' => $this->getType(),
      'reason' => $this->getReasonText(),
      'class' => $this->getClass(),
      'status' => $this->getStatus(),
      'location' => $this->getLocation(),
      'period-start' => $periodStart = $this->getPeriodStart(),
      'normalized_period-start' => $this->getGmtTimestamp($periodStart, self::TIMESTAMP_FORMAT),
      'period-end' => $periodEnd = $this->getPeriodEnd(),
      'normalized_period-end' => $this->getGmtTimestamp($periodEnd, self::TIMESTAMP_FORMAT),

    ];
    return $data;
  }
  
}