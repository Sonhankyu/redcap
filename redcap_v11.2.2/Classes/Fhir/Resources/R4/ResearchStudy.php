<?php
namespace Vanderbilt\REDCap\Classes\Fhir\Resources\R4;

use Vanderbilt\REDCap\Classes\Fhir\Resources\AbstractResource;
use Vanderbilt\REDCap\Classes\Fhir\Resources\Shared\CodeableConcept;
use Vanderbilt\REDCap\Classes\Fhir\Resources\Traits\CanNormalizeTimestamp;

class ResearchStudy extends AbstractResource
{
  
  public function getId()
  {
    return $this->query()
      ->select('#/id')
      ->results()
      ->single();
  }

  public function getTitle()
  {
    return $this->query()
      ->select('#/title')
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

  public function getPrincipalInvestigator()
  {
    return $this->query()
      ->select('#/principalInvestigator/display')
      ->results()
      ->single();
  }

  public function getData()
  {
    $data = [
      'id' => $this->getId(),
      'title' => $this->getTitle(),
      'status' => $this->getStatus(),
      'principal-investigator' => $this->getPrincipalInvestigator(),
    ];
    return $data;
  }
  
}