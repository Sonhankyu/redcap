<?php
namespace Vanderbilt\REDCap\Classes\Fhir\FhirMetadata\Decorators;

class FhirMetadataEmailDecorator extends FhirMetadataAbstractDecorator
{
  /**
   * apply decorator and get a new list
   *
   * @param array $list
   * @return array
   */
  public function getList()
  {
    $systemConfig = \System::getConfigVals();
    $fhir_include_email_address = boolval(@$systemConfig['fhir_include_email_address']);
    
    $projectConfig = \Project::getProjectVals();
    $fhir_include_email_address_project = boolval(@$projectConfig['fhir_include_email_address_project']);

    if (!$fhir_include_email_address || !$fhir_include_email_address_project) {
      $metadata_array = $this->fhirMetadata->getList();
      $emailKeys = ['email', 'email-2', 'email-3'];
      $whereIsDisabled = [];
      if(!$fhir_include_email_address) $whereIsDisabled[] = 'system level';
      if(!$fhir_include_email_address_project) $whereIsDisabled[] = 'project level';
      $reason = sprintf('`Emails` fetching has been disabled at %s.', implode(' and ', $whereIsDisabled));
      foreach ($emailKeys as $key) {
        if(!array_key_exists($key, $metadata_array)) continue; // only process if the key exists
        $metadata_array[$key]['disabled'] = true;
        $metadata_array[$key]['disabled_reason'] = $reason;
      }
      return $metadata_array;
    }

    return $this->fhirMetadata->getList();
  }
}