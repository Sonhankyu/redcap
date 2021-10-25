<?php
namespace Vanderbilt\REDCap\Classes\Fhir\Resources\DSTU2;

use Vanderbilt\REDCap\Classes\Fhir\Resources\AbstractResource;
use Vanderbilt\REDCap\Classes\Fhir\Resources\Shared\CodeableConcept;
use Vanderbilt\REDCap\Classes\Fhir\Resources\Shared\CodingSystem;
use Vanderbilt\REDCap\Classes\Fhir\Resources\Traits\CanNormalizeTimestamp;

class MedicationOrder extends AbstractResource
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

  public function getDosageText()
  {
    return $this->query()
      ->select('#/dosageInstruction/\d+/text')
      ->results()
      ->single();
  }

  public function getDosageInstructionRoute()
  {
    return $this->query()
      ->select('#/dosageInstruction/\d+/route/text')
      ->results()
      ->single();
  }

  public function getDosageInstructionTiming()
  {
    return $this->query()
      ->select('#/dosageInstruction/\d+/timing/.*/text')
      ->results()
      ->single();
  }

  public function getDosageInstructionTimingAlternateQuery()
  {
    return $this->query()
      ->select('#/dosageInstruction/\d+/timing')
      ->select('text')
      ->results()
      ->single();
  }

  public function getDateWritten()
  {
    return $this->query()
      ->select('#/dateWritten$')
      ->results()
      ->single();
  }

  public function getPrescriber()
  {
    return $this->query()
      ->select('#/prescriber/display')
      ->results()
      ->single();
  }

  public function getMedicationReference()
  {
    return $this->query()
      ->select('#/medicationReference/display')
      ->results()
      ->single();
  }

  public function getMedicationCodeableConcept()
  {
    $payload = $this->query()
      ->select('#/medicationCodeableConcept$')
      ->results()
      ->expand();
    return new CodeableConcept('medicationCodeableConcept', $payload);
  }

  public function rxnormDisplay()
  {
    return $this->query()
      ->select('#/medicationCodeableConcept/coding/\d+$')
      ->where('system', 'like', CodingSystem::RxNorm)
      ->orWhere('system', 'like', CodingSystem::RxNorm_2)
      ->select('display$')
      ->results()
      ->single();
  }

  public function rxnormCode()
  {
    return $this->query()
      ->select('#/medicationCodeableConcept/coding/\d+$')
      ->where('system', 'like', CodingSystem::RxNorm)
      ->orWhere('system', 'like', CodingSystem::RxNorm_2)
      ->select('code$')
      ->results()
      ->single();
  }

  public function split()
  {
    $codeableConcept = $this->getMedicationCodeableConcept();
    $codings = $codeableConcept->getCoding();
    if(count($codings)<=1) return [$this];
    $text = $codeableConcept->getText();
    $list = array_map(function($coding) use($text) {
      // make a new code payload that will replace the existing one
      $payload = [
        'coding' => [$coding],
        'text' => $text,
      ];
      return $this->replacePayload('medicationCodeableConcept', $payload);
    }, $codings);
    return $list;
  }

  public function getNormalizedTimestamp()
  {
    $timestamp = $this->getDateWritten();
    return $this->getGmtTimestamp($timestamp, self::TIMESTAMP_FORMAT);
  }

  public function getText()
  {
    // add medication concept if available
    $medicationCodeableConcept = $this->getMedicationCodeableConcept();
    return $medicationCodeableConcept->getText();
  }

  public function getData()
  {
    $medicationCodeableConcept = $this->getMedicationCodeableConcept();
    $medicationCodeableConceptData = $medicationCodeableConcept->isEmpty() ? '' : $medicationCodeableConcept->getData();
    $data = [
      'status' => $this->getStatus(),
      'display' => $this->getMedicationReference(),
      'timestamp' => $this->getDateWritten(),
      'normalized_timestamp' => $this->getNormalizedTimestamp(), // convert to proper date
      'dosage' => $this->getDosageText(),
      'dosage_instruction_route' => $this->getDosageInstructionRoute(),
      'dosage_instruction_timing' => $this->getDosageInstructionTiming(),
      'rxnorm_code' => $this->rxnormDisplay(),
      'rxnorm_display' => $this->rxnormCode(),
      'medicationCodeableConcept' => $medicationCodeableConceptData,
      'text' => $this->getText(),
    ];
    return $data;
  }
  
}