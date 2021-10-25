<?php namespace Vanderbilt\REDCap\Classes\Fhir;

use DateInterval;
use DateTime;
use DateTimeZone;
use Vanderbilt\REDCap\Classes\Fhir\Endpoints\EndpointVisitorInterface;
use Vanderbilt\REDCap\Classes\Fhir\Endpoints\AbstractEndpoint;
use Vanderbilt\REDCap\Classes\Fhir\Endpoints\DSTU2\Condition;
use Vanderbilt\REDCap\Classes\Fhir\Endpoints\DSTU2\Patient as Patient_DSTU2;
use Vanderbilt\REDCap\Classes\Fhir\Endpoints\DSTU2\MedicationOrder;
use Vanderbilt\REDCap\Classes\Fhir\Endpoints\DSTU2\ObservationLabs as ObservationLabs_DSTU2;
use Vanderbilt\REDCap\Classes\Fhir\Endpoints\DSTU2\ObservationSmokingHistory as ObservationSmokingHistory_DSTU2;
use Vanderbilt\REDCap\Classes\Fhir\Endpoints\DSTU2\ObservationVitals as ObservationVitals_DSTU2;
use Vanderbilt\REDCap\Classes\Fhir\Endpoints\FhirRequest;
use Vanderbilt\REDCap\Classes\Fhir\Endpoints\R4\Patient as Patient_R4;
use Vanderbilt\REDCap\Classes\Fhir\Endpoints\R4\AdverseEvent;
use Vanderbilt\REDCap\Classes\Fhir\Endpoints\R4\MedicationRequest;
use Vanderbilt\REDCap\Classes\Fhir\Endpoints\R4\AllergyIntolerance as AllergyIntolerance_R4;
use Vanderbilt\REDCap\Classes\Fhir\Endpoints\R4\ConditionProblems;
use Vanderbilt\REDCap\Classes\Fhir\Endpoints\R4\Encounter;
use Vanderbilt\REDCap\Classes\Fhir\Endpoints\R4\ObservationLabs as ObservationLabs_R4;
use Vanderbilt\REDCap\Classes\Fhir\Endpoints\R4\ObservationSmokingHistory as ObservationSmokingHistory_R4;
use Vanderbilt\REDCap\Classes\Fhir\Endpoints\R4\ObservationVitals as ObservationVitals_R4;
use Vanderbilt\REDCap\Classes\Fhir\Endpoints\R4\ObservationCoreCharacteristics;
use Vanderbilt\REDCap\Classes\Fhir\Endpoints\R4\ResearchStudy;
use Vanderbilt\REDCap\Classes\Fhir\Resources\R4\ResearchStudy as R4ResearchStudy;
use Vanderbilt\REDCap\Classes\Fhir\Resources\Shared\Bundle;
use Vanderbilt\REDCap\Classes\Fhir\TokenManager\FhirTokenManager;
use Vanderbilt\REDCap\Classes\Fhir\Utility\FileCache;

/**
 * FHIR endpoint visitor that generates parameters
 * for FHIR endpoints using REDCap mapping, projects settings
 * and system settings.
 */
class RedcapEndpointVisitor implements EndpointVisitorInterface
{

  /**
   *
   * @var FhirClient
   */
  private $fhirClient;

  /**
   * @var string
   */
  private $patient_id;
  
  /**
   * @var array
   */
  private $fields;

  /**
   * @var array [DateTime, DateTime]
   */
  private $dateRange;

  /**
   *
   * @param string $patient_id
   * @param array $fields
   * @param DateTime $dateMin
   * @param DateTime $dateMax
   * @param FhirTokenManager $fhirTokenManager
   */
  function __construct($fhirClient, $patient_id, $fields, $dateMin, $dateMax)
  {
    $this->fhirClient = $fhirClient;
    $this->patient_id = $patient_id;
    $this->fields = $fields;
    $this->dateRange = $this->makeDateRange($dateMin, $dateMax);
  }

  /**
   * adjust the options for the endpoint
   * @param AbstractEndpoint $endpoint
   * @return array
   */
  function visit($endpoint)
  {
    $options = $this->options;
    $class = get_class($endpoint);
    switch ($class) {
      case Patient_DSTU2::class:
      case Patient_R4::class:
        $options = $this->visitPatient($endpoint);
        break;
      case AdverseEvent::class:
        $options = $this->visitAdverseEvents($endpoint);
        break;
      case AllergyIntolerance_R4::class:
        $options = $this->visitAllergyR4($endpoint);
        break;
      case Condition::class:
        $options = $this->visitCondition($endpoint);
        break;
      case ConditionProblems::class:
        $options = $this->visitConditionProblems($endpoint);
        break;
      case MedicationOrder::class:
      case MedicationRequest::class:
        $options = $this->visitMedications($endpoint);
        break;
      case ObservationSmokingHistory_DSTU2::class:
      case ObservationSmokingHistory_R4::class:
      case ObservationVitals_DSTU2::class:
      case ObservationVitals_R4::class:
      case ObservationLabs_DSTU2::class:
      case ObservationLabs_R4::class:
      case ObservationCoreCharacteristics::class:
        $options = $this->visitObservaion($endpoint);
        break;
      case MedicationRequest::class:
        $options = $this->visitMedications($endpoint);
        break;
      case Encounter::class:
        $options = $this->visitEncounter($endpoint);
        break;
      default:
        $options['patient'] = $this->patient_id;
        break;
    }
    return $options;
  }

  /**
   *
   * @param Patient_DSTU2|Patient_R4 $endpoint
   * @return array
   */
  public function visitPatient($endpoint)
  {
    $options['_id'] = $this->patient_id;
    return $options;
  }

  /**
   *
   * @param AllergyIntolerance_R4 $endpoint
   * @return array
   */
  public function visitAllergyR4($endpoint)
  {
    $options['patient'] = $this->patient_id;
    $options['clinical-status'] = $endpoint::CLINICAL_STATUS_ACTIVE;
    return $options;
  }

  /**
   *
   * @param AdverseEvent $endpoint
   * @return array
   */
  public function visitAdverseEvents($endpoint)
  {
    $getProjectIrbNumber = function() {
      $projectVals = \Project::getProjectVals();
      $irbNumber = @$projectVals['project_irb_number'];
      $purpose = intval(@$projectVals['purpose']);
      if($purpose!=2 || empty($irbNumber)) return;
      return $irbNumber;
    };

    $irbNumber = $getProjectIrbNumber();
    if(empty($irbNumber)) return;

    $fileCache = new FileCache(__CLASS__);
    $studyIdCacheKey = 'irb_number_'.$irbNumber;
    if(!$studyFhirId=$fileCache->get($studyIdCacheKey)) {
      $studyFhirId = $this->fhirClient->getStudyFhirId($irbNumber);
      if(!$studyFhirId) return;
      $fileCache->set($studyIdCacheKey, $studyFhirId);
    }

    $options['study'] = $studyFhirId;
    $options['subject'] = $this->patient_id;
    return $options;
  }

  /**
   *
   * @param Condition $endpoint
   * @return array
   */
  public function visitCondition($endpoint)
  {
    $options['patient'] = $this->patient_id;
    return $options;
  }
  /**
   *
   * @param ConditionProblems $endpoint
   * @return array
   */
  public function visitConditionProblems($endpoint)
  {
    $options['patient'] = $this->patient_id;
    $options['clinical-status'] = ConditionProblems::CLINICAL_STATUS_ACTIVE;
    return $options;
  }

  /**
   *
   * @param AbstractObservation_DSTU2|AbstractObservation_R4 $endpoint
   * @return array
   */
  public function visitObservaion($endpoint)
  {
    $options['patient'] = $this->patient_id;
    $options['date'] = $this->dateRange;
    return $options;
  }

  /**
   *
   * @param Encounter $endpoint
   * @return array
   */
  public function visitEncounter($endpoint)
  {
    $options['patient'] = $this->patient_id;
    $options['date'] = $this->dateRange;
    return $options;
  }

  /**
   * Undocumented function
   *
   * @param MedicationOrder|MedicationRequest $endpoint
   * @return void
   */
  public function visitMedications($endpoint)
  {
    $options['patient'] = $this->patient_id;
    $options['status'] = $endpoint->getStatusParam($this->fields);
    return $options;
  }

  /**
   * create a date range to use when filtering by date
   *
   * @param DateTime $date_min
   * @param DateTime $date_max
   * @return array
   * 
   * @see https://www.hl7.org/fhir/search.html#date
   */
  protected function makeDateRange($date_min, $date_max)
  {
    /**
     * apply the system timezone to a date
     * @param DateTime $date
     */
    $applyTimeZone = function($date) {
      if(!($date instanceof DateTime)) $date = new DateTime($date);
      $systemTimezone = new DateTimeZone(getTimeZone());
      $gmtTimezone = new DateTimeZone('GMT');
      $modifiedDate = clone $date;
      $modifiedDate->setTimezone($gmtTimezone);
      $offset = $systemTimezone->getOffset($modifiedDate);
      $interval = DateInterval::createFromDateString((string)$offset . ' seconds');
      $date->add($interval);
      return $date;
    };
    /**
     * Where possible, the system should correct for time zones when performing queries.
     * Dates do not have time zones, and time zones should not be considered.
     * Where both search parameters and resource element date times do not have time zones,
     * the servers local time zone should be assumed.
     * 
     * @see https://www.hl7.org/fhir/search.html#date
     */
    $convertFromGmt = function(&$date_min, &$date_max) use($applyTimeZone){
      $configVals = \System::getConfigVals();
      $fhir_convert_timestamp_from_gmt = boolval(@$configVals['fhir_convert_timestamp_from_gmt']);
      if(!$fhir_convert_timestamp_from_gmt) return;
      if($date_min instanceof \DateTime) $date_min = $applyTimeZone($date_min);
      if($date_max instanceof \DateTime) $date_max = $applyTimeZone($date_max);
    };

    $convertFromGmt($date_min, $date_max);
    
    $fhir_datetime_format = "Y-m-d\TH:i:s\Z";
    $params = [];
    if($date_min instanceof DateTime) $params[] = "ge{$date_min->format($fhir_datetime_format)}";
    if($date_max instanceof DateTime) $params[] = "le{$date_max->format($fhir_datetime_format)}";
    return $params;
  }

}