<?php
namespace Vanderbilt\REDCap\Classes\Fhir\FhirMetadata\Decorators;

/**
 * decorator made specifically for CDP projects
 */
class FhirMetadataCdpDecorator extends FhirMetadataAbstractDecorator
{

  /**
   * apply decorator and get a new list
   *
   * @param array $list
   * @return array
   */
  public function getList()
  {
    $metadata_array = $this->fhirMetadata->getList();
    $disableEncounters = function($key, $metadata_array) {
      $metadata_array[$key]['disabled'] = true;
      $metadata_array[$key]['disabled_reason'] = '`Encounters` are not available for `Clinical Data Pull` type projects.';
      return $metadata_array;
    };
    $key = 'encounters-list';
    if(array_key_exists($key, $metadata_array)) {
      $metadata_array = $disableEncounters($key, $metadata_array);
    }
    return $metadata_array;
  }
}