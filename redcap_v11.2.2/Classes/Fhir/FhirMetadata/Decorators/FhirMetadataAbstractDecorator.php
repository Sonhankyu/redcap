<?php
namespace Vanderbilt\REDCap\Classes\Fhir\FhirMetadata\Decorators;

use Vanderbilt\REDCap\Classes\Fhir\FhirMetadata\FhirMetadataSource;

abstract class FhirMetadataAbstractDecorator extends FhirMetadataSource implements FhirMetadataDecoratorInterface
{

  protected $fhirMetadata;
  
  /**
   * @param FhirMetadata $fhirMetadata
   */
  public function __construct($fhirMetadata)
  {

    $this->fhirMetadata = $fhirMetadata;
  }
}