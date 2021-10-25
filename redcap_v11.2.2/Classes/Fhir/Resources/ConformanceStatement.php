<?php
namespace Vanderbilt\REDCap\Classes\Fhir\Resources;

/**
 * Factory for factories.
 * Get the factory that will build endpoints for a specific FHIR version.
 */
class ConformanceStatement extends AbstractResource
{

  public function getFhirVersion()
  {
    return $this->query()->select('#/fhirVersion$')->results()->single();
  }

   public function getData()
   {
     return [
      'fhirVersion' => $this->getFhirVersion(),
     ];
   }
}