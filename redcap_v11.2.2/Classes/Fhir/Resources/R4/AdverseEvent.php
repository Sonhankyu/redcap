<?php
namespace Vanderbilt\REDCap\Classes\Fhir\Resources\R4;

use Vanderbilt\REDCap\Classes\Fhir\Resources\AbstractResource;
use Vanderbilt\REDCap\Classes\Fhir\Resources\TimestampInterface;
use Vanderbilt\REDCap\Classes\Fhir\Resources\Traits\CanNormalizeTimestamp;

class AdverseEvent extends AbstractResource
{
  use CanNormalizeTimestamp;

  const TIMESTAMP_FORMAT = 'Y-m-d H:i';

  /**
   * get the local or GMT timestamp
   * 
   * @param boolean $localTimestamp
   * @return string
   */
  public function getTimestamp($localTimestamp=false)
  {
    $callable = $this->getTimestampCallable($this->getDate(), self::TIMESTAMP_FORMAT);
    return $callable($localTimestamp);
  }

  public function getActuality()
  {
    return $this->query()
      ->select('#/actuality')
      ->results()
      ->single();
  }

  public function getEvent()
  {
    return $this->query()
      ->select('#/event/text')
      ->results()
      ->single();
  }

  public function getSeriousness()
  {
    return $this->query()
      ->select('#/seriousness/text')
      ->results()
      ->single();
  }

  public function getSeverity()
  {
    return $this->query()
      ->select('#/severity/text')
      ->results()
      ->single();
  }

  public function getOutcome()
  {
    return $this->query()
      ->select('#/outcome/text')
      ->results()
      ->single();
  }

  public function getDate()
  {
    return $this->query()
      ->select('#/recordedDate')
      ->results()
      ->single();
  }

  public function getStudies()
  {
    return $this->query()
      ->select('#/study/\d+/display')
      ->results()
      ->join(', ');
  }

  public function getNormalizedTimestamp()
  {
    $timestamp = $this->getDate();
    return $this->getGmtTimestamp($timestamp, self::TIMESTAMP_FORMAT);
  }

  public function getData()
  {
    $data = [
      'actuality' => $this->getActuality(),
      'event' => $this->getEvent(),
      'seriousness' => $this->getSeriousness(),
      'severity' => $this->getSeverity(),
      'outcome' => $this->getOutcome(),
      'studies' => $this->getStudies(),
      'timestamp' => $this->getDate(),
      'normalized_timestamp' => $this->getNormalizedTimestamp(), // convert to proper date
    ];
    return $data;
  }
  
}