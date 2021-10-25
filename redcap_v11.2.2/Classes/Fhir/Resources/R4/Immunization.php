<?php
namespace Vanderbilt\REDCap\Classes\Fhir\Resources\R4;

use Vanderbilt\REDCap\Classes\Fhir\Resources\AbstractResource;
use Vanderbilt\REDCap\Classes\Fhir\Resources\Shared\CodingSystem;
use Vanderbilt\REDCap\Classes\Fhir\Resources\Traits\CanNormalizeTimestamp;

class Immunization extends AbstractResource
{
  use CanNormalizeTimestamp;

  const TIMESTAMP_FORMAT = 'Y-m-d';

  public function getStatus()
  {
    return $this->query()
      ->select('#/status$')
      ->results()
      ->single();
  }

  public function getText()
  {
    return $this->query()
      ->select('#/vaccineCode/text$')
      ->results()
      ->single();
  }

  public function getDate()
  {
    return $this->query()
      ->select('#/occurrenceDateTime$')
      ->results()
      ->single();
  }

  public function getCvxCode()
  {
    return $this->query()
      ->select('#/vaccineCode/coding/\d+')
      ->where('system', 'like', CodingSystem::CVX)
      ->select('code')
      ->results()
      ->single();
  }


  public function getNormalizedTimestamp()
  {
    $timestamp = $this->getDate();
    return $this->getGmtTimestamp($timestamp, self::TIMESTAMP_FORMAT);
  }

  public function getData()
  {
    $data = [
      'text' => $this->getText(),
      'date' => $this->getDate(),
      'normalized_date' => $this->getNormalizedTimestamp(),
      'status' => $this->getStatus(),
      'cvx_code' => $this->getCvxCode(),
    ];
    return $data;
  }
  
}