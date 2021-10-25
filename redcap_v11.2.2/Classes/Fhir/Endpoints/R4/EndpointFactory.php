<?php
namespace Vanderbilt\REDCap\Classes\Fhir\Endpoints\R4;


use Vanderbilt\REDCap\Classes\Fhir\Endpoints\AbstractEndpoint;
use Vanderbilt\REDCap\Classes\Fhir\Endpoints\AbstractEndpointFactory;
use Vanderbilt\REDCap\Classes\Fhir\FhirCategory;

class EndpointFactory extends AbstractEndpointFactory
{

  /**
   * get an endpoint
   *
   * @param string $category
   * @return AbstractEndpoint
   */
  public function makeEndpoint($category)
  {

    $base_url = $this->getBaseUrl();
    switch ($category) {
      case FhirCategory::ALLERGY_INTOLERANCE:
        $endpoint = new AllergyIntolerance($base_url);
        break;
      case FhirCategory::ADVERSE_EVENT:
        $endpoint = new AdverseEvent($base_url);
        break;
      case FhirCategory::DEMOGRAPHICS:
        $endpoint = new Patient($base_url);
        break;
      case FhirCategory::CONDITION:
        $endpoint = new ConditionProblems($base_url);
        break;
      case FhirCategory::CORE_CHARACTERISTICS:
        $endpoint = new ObservationCoreCharacteristics($base_url);
        break;
      case FhirCategory::ENCOUNTER:
        $endpoint = new Encounter($base_url);
        break;
      case FhirCategory::IMMUNIZATION:
        $endpoint = new Immunization($base_url);
        break;
      case FhirCategory::MEDICATIONS:
        $endpoint = new MedicationRequest($base_url);
        break;
      case FhirCategory::LABORATORY:
        $endpoint = new ObservationLabs($base_url);
        break;
      case FhirCategory::VITAL_SIGNS:
        $endpoint = new ObservationVitals($base_url);
        break;
      case FhirCategory::SMOKING_HISTORY:
        $endpoint = new ObservationSmokingHistory($base_url);
        break;
      case FhirCategory::RESEARCH_STUDY:
        $endpoint = new ResearchStudy($base_url);
        break;
      default:
        $endpoint = null;
        break;
    }
    return $endpoint;
  }

}