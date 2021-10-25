<?php
namespace Vanderbilt\REDCap\Classes\Fhir\Resources\Shared;

use Vanderbilt\REDCap\Classes\Fhir\Resources\AbstractResource;

class Condition extends AbstractResource
{

  public function getClinicalStatus()
  {
    return $this->query()
      ->select('#/clinicalStatus$')
      ->results()
      ->single();
  }

  public function getClinicalStatusCode()
  {
    return $this->query()
    ->select('#/clinicalStatus/coding/.+/code$')
    ->results()
    ->single();
  }

  public function getDateRecorded()
  {
    return $this->query()
    ->select('#/dateRecorded')
    ->results()
    ->single();
  }

  public function getVerificationStatus()
  {
    return $this->query()
    ->select('#/verificationStatus/coding/.+/code')
    ->results()
    ->single();
  }

  public function getLabel()
  {
    return $this->query()
      ->select('#/code/text')
      ->results()
      ->single();
  }

  /**
   *
   * @return CodeableConcept
   */
  public function getCode()
  {
    $payload = $this->query()
      ->select('#/code$')
      ->results()
      ->expand();
    return new CodeableConcept('code', $payload);
  }

  public function getCodingSystemSnomedCt()
  {
    return $this->query()
      ->select('#/code/coding/\d+$')
      ->where('system', 'like', CodingSystem::SNOMED_CT)
      ->orWhere('system', 'like', CodingSystem::SNOMED_CT_1)
      ->results()
      ->group(0);
  }

  /**
   * create a CodeableConcept from the code
   * portion of the payload
   *
   * @return CodeableConcept
   */
  public function getIcd10()
  {
    return $this->query()
      ->select('#/code/coding/\d+$')
      ->where('system', 'like', CodingSystem::ICD_10_CM)
      ->orWhere(
        ['system', 'like', CodingSystem::ICD_10_INTERNATIONAL_WHO],
        ['system', 'like', CodingSystem::ICD_10_INTERNATIONAL_WHO_DUTCH_VARIANT],
        ['system', 'like', CodingSystem::ICD_10_AE],
        ['system', 'like', CodingSystem::ICD_10_PCS]
      )
      ->results()
      ->expand(0);
  }

  /**
   * create a CodeableConcept from the code
   * portion of the payload
   *
   * @return CodeableConcept
   */
  public function getIcd9()
  {
    return $this->query()
      ->select('#/code/coding/\d+$')
      ->where('system', 'like', CodingSystem::ICD_9_CM)
      ->results()
      ->expand(0);
  }

  /**
   * create a CodeableConcept from the code
   * portion of the payload
   *
   * @return CodeableConcept
   */
  public function getSnomed()
  {
    return $this->query()
      ->select('#/code/coding/\d+$')
      ->where('system', 'like', CodingSystem::SNOMED_CT)
      ->orWhere('system', 'like', CodingSystem::SNOMED_CT_1)
      ->results()
      ->expand(0);
  }

  public function getDisplayIcd10()
  {
    return $this->query()
      ->select('#/code/coding/\d+$')
      ->where('system', 'like', CodingSystem::ICD_10_CM)
      ->orWhere(
        ['system', 'like', CodingSystem::ICD_10_INTERNATIONAL_WHO],
        ['system', 'like', CodingSystem::ICD_10_INTERNATIONAL_WHO_DUTCH_VARIANT],
        ['system', 'like', CodingSystem::ICD_10_AE],
        ['system', 'like', CodingSystem::ICD_10_PCS]
      )
      ->select('display$')
      ->results()
      ->single();
  }

  public function getCodeIcd10()
  {
    return $this->query()
      ->select('#/code/coding/\d+$')
      ->where('system', 'like', CodingSystem::ICD_10_CM)
      ->orWhere(
        ['system', 'like', CodingSystem::ICD_10_INTERNATIONAL_WHO],
        ['system', 'like', CodingSystem::ICD_10_INTERNATIONAL_WHO_DUTCH_VARIANT],
        ['system', 'like', CodingSystem::ICD_10_AE],
        ['system', 'like', CodingSystem::ICD_10_PCS]
      )
      ->select('code$')
      ->results()
      ->single();
  }

  public function getDisplayIcd9()
  {
    return $this->query()
      ->select('#/code/coding/\d+$')
      ->where('system', 'like', CodingSystem::ICD_9_CM)
      ->select('display$')
      ->results()
      ->single();
  }

  public function getCodeIcd9()
  {
    return $this->query()
      ->select('#/code/coding/\d+$')
      ->where('system', 'like', CodingSystem::ICD_9_CM)
      ->select('code$')
      ->results()
      ->single();
  }

  public function getDisplaySnomedCt()
  {
    return $this->query()
      ->select('#/code/coding/\d+$')
      ->where('system', 'like', CodingSystem::SNOMED_CT)
      ->orWhere('system', 'like', CodingSystem::SNOMED_CT_1)
      ->select('display$')
      ->results()
      ->single();
  }

  public function getCodeSnomedCt()
  {
    return $this->query()
      ->select('#/code/coding/\d+$')
      ->where('system', 'like', CodingSystem::SNOMED_CT)
      ->orWhere('system', 'like', CodingSystem::SNOMED_CT_1)
      ->select('code$')
      ->results()
      ->single();
  }

  public function getData()
  {
    $data = [
      'clinical-status'       => $this->getClinicalStatus() ?: ($this->getClinicalStatusCode() ?: ''),
      'timestamp'             => $this->getDateRecorded(),
      'label'                 => $label = $this->getLabel(),
      // 'code'                  => $this->getCode()->getData(),
      // 'icd-10'                => $this->getCodeIcd10(),
      // 'icd-9'                 => $this->getCodeIcd9(),
      // 'snomed'                => $this->getCodeSnomedCt(),
      'icd-10-code'           => $icd10_code = $this->getCodeIcd10(),
      'icd-10-display'        => $this->getDisplayIcd10() ?: ($icd10_code ? $label : ''), //fallback to label is code and !display
      'icd-9-code'            => $icd9_code =$this->getCodeIcd9(),
      'icd-9-display'         => $this->getDisplayIcd9() ?: ($icd9_code ? $label : ''), //fallback to label is code and !display
      'snomed-ct-code'        => $snomedCt_code = $this->getCodeSnomedCt(),
      'snomed-ct-display'     => $this->getDisplaySnomedCt() ?: ($snomedCt_code ? $label : ''), //fallback to label is code and !display
      'verification-status'   => $this->getVerificationStatus(),
      // 'icd10-cm'              => $this->getIcd10(),
      // 'icd9-cm'               => $this->getIcd9(),
      // 'snomed-ct'             => $this->getSnomed(),
    ];
    return $data;
  }
  
}