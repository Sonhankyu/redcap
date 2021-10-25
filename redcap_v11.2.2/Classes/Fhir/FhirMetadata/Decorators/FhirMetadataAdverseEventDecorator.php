<?php
namespace Vanderbilt\REDCap\Classes\Fhir\FhirMetadata\Decorators;

class FhirMetadataAdverseEventDecorator extends FhirMetadataAbstractDecorator
{
  /**
   * apply decorator and get a new list
   *
   * @param array $list
   * @return array
   */
  public function getList()
  {
    $projectConfig = \Project::getProjectVals();
    $irbNumber = @$projectConfig['project_irb_number'];
    $purpose = intval(@$projectConfig['purpose']);
    $metadata_array = $this->fhirMetadata->getList();
    $found = array_key_exists('adverse-events-list', $metadata_array);

    if($found && ($purpose!==2 || !$irbNumber)) {
      //do something with adverse event
      $metadata_array['adverse-events-list']['disabled'] = true;
      $metadata_array['adverse-events-list']['disabled_reason'] = '`Adverse Events` are only available for `research` projects where an IRB number is specified.';
      // unset($metadata_array['adverse-events-list']);
    }

    return $metadata_array;
  }
}