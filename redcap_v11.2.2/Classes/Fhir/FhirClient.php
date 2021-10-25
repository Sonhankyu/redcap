<?php
namespace Vanderbilt\REDCap\Classes\Fhir;

use Exception;
use Vanderbilt\REDCap\Classes\Fhir\Endpoints\EndpointFactoryInterface;
use Vanderbilt\REDCap\Classes\Fhir\Endpoints\FhirRequest;
use Vanderbilt\REDCap\Classes\Fhir\FhirVersionManager;
use Vanderbilt\REDCap\Classes\Fhir\Endpoints\AbstractEndpointFactory;
use Vanderbilt\REDCap\Classes\Fhir\FhirCategory;
use Vanderbilt\REDCap\Classes\Fhir\Endpoints\Traits\CanRemoveExtraSlashesFromUrl;
use Vanderbilt\REDCap\Classes\Fhir\FhirMetadata\FhirMetadataSource;
use Vanderbilt\REDCap\Classes\Fhir\Resources\AbstractResource;
use Vanderbilt\REDCap\Classes\Fhir\Resources\Shared\Bundle;
use Vanderbilt\REDCap\Classes\Fhir\Resources\Shared\Patient;
use Vanderbilt\REDCap\Classes\Fhir\TokenManager\FhirTokenManager;
use Vanderbilt\REDCap\Classes\Fhir\Traits\SubjectTrait;
use Vanderbilt\REDCap\Classes\Fhir\Endpoints\R4\ResearchStudy;
use Vanderbilt\REDCap\Classes\Fhir\Resources\R4\ResearchStudy as ResearchStudyResource;

class FhirClient
{
  use CanRemoveExtraSlashesFromUrl;
  use SubjectTrait;

  /** tags for notifications */
  
  const NOTIFICATION_PATIENT_IDENTIFIED = 'FhirClient:patient_identified';
  const NOTIFICATION_ENTRIES_RECEIVED = 'FhirClient:entries_received';
  const NOTIFICATION_ERROR = 'FhirClient:error';
  const NOTIFICATION_REQUEST_SENT = 'FhirClient:request_sent';
  const NOTIFICATION_REQUEST_ERROR = 'FhirClient:request_error';

  /**
   * current project
   *
   * @var int
   */
  private $project_id;

  /**
   * current user
   *
   * @var int
   */
  private $user_id;

  /**
   * BASE FHIR URL
   * @var string
   */
  private $base_url;

  /**
   * REDCap settings
   * @var array
   */
  private $system_configs;

  /**
   * @var FhirVersionManager
   */
  private $fhirVersionManager;

  /**
   * @var AbstractEndpointFactory
   */
  private $endpointFactory;


  /**
   * list of errors occourred in the process
   * of fetching and processing FHIR resources
   *
   * @var array
   */
  private $errors = [];

  /**
   * Fhir Token Manager
   *
   * @var FhirTokenManager
   */
  private $fhirTokenManager;


  /**
   * create a FHIR client
   *
   * @param int $project_id
   * @param FhirTokenManager $fhirTokenManager
   */
  public function __construct($project_id, $fhirTokenManager)
  {
    $this->project_id = $project_id;
    $this->fhirTokenManager = $fhirTokenManager;
    $this->system_configs = \System::getConfigVals();
    $this->fhirVersionManager = FhirVersionManager::getInstance();
  }

  public function getBaseUrl()
  {
    return $this->fhirVersionManager->getBaseUrl();
  }

  public function getFhirRequest($relative_url, $method='GET', $options=[])
  {
    $base_url = $this->fhirVersionManager->getBaseUrl();
    $URL = $this->removeExtraSlashesFromUrl(sprintf("%s/%s", $base_url, $relative_url));
    $fhir_request = new FhirRequest($URL, $method);
    $fhir_request->setOptions($options);
    return $fhir_request;
  }

  /**
   * use the study ID to find its FHIR id
   *
   * @param string $studyIdentifier
   * @param FhirClient $fhirClient
   * @return string|null
   */
  public function getStudyFhirId($studyIdentifier)
  {
      $endpoint = new ResearchStudy($this->getBaseUrl());
      $searchRequest = $endpoint->getSearchRequest(['identifier'=>$studyIdentifier]);
      $bundle = $this->getResource($searchRequest);
      if(!($bundle instanceof Bundle)) return;
      $entries = $bundle->getEntries();
      $entry = reset($entries);
      if(!($entry instanceof ResearchStudyResource)) return;
      $studyFhirId = $entry->getId();
      if(!$studyFhirId) return;
      return $studyFhirId;
  }

  /**
   * get a patient ID using an MRN.
   * the patient ID could be
   * - the MRN itself if no patient_string_identifier is set in REDCap settings
   * - retrieved from the database in redcap_ehr_access_tokens if previously cached
   * - retrieved remotely using the patient.search FHIR endpoint
   *
   * @param string $mrn
   * @return string
   */
  public function getPatientID($mrn)
  {
    $searchPatientByMrn = function($mrn, $patient_string_identifier) {
      $identifier = "{$patient_string_identifier}|{$mrn}";
      $params = ['identifier' => $identifier];
      
      $endpointFactory = $this->getEndpointFactory();
      if(!$endpointFactory) return;
      $endpoint = $endpointFactory->makeEndpoint(FhirCategory::DEMOGRAPHICS);
      $request = $endpoint->getSearchRequest($params);
      $resource = $this->getResource($request);
      if(!($resource instanceof Bundle)) return;
      $patient_entries = $resource->getEntries();
      $patient_resource = reset($patient_entries);
      if(!($patient_resource instanceof Patient)) return;
      $fhir_id = $patient_resource->getFhirID();
      $this->notify(self::NOTIFICATION_PATIENT_IDENTIFIED, compact('mrn', 'fhir_id'));
      return $fhir_id;
    };
    $searchPatientOnDatabase = function($mrn) {
      $query_string = sprintf(
        "SELECT patient FROM redcap_ehr_access_tokens WHERE mrn=%s",
        db_escape($mrn)
      );
      $result = db_query($query_string);
      if($row = db_fetch_assoc($result)) return $row['patient'];
      return false;
    };

    $patient_string_identifier = @$this->system_configs['fhir_ehr_mrn_identifier'];
    if(empty($patient_string_identifier)) return $mrn; // use the MRN
    else if($patient_id = $searchPatientOnDatabase($mrn)) return $patient_id; // check on database
    else if($patient_id = $searchPatientByMrn($mrn, $patient_string_identifier)) return $patient_id; // try remote search
    $patient_not_found = new \Exception("No FHIR ID could be found for the MRN {$mrn}", 404);
    $this->addError($patient_not_found);
    return false;
  }

  /**
   * extract resources from Bundles and load extra data
   *
   * @param Bundle $bundle
   * @param AbstractResource[] $resources
   * @return AbstractResource[] entries
   */
  public function unzipBundleAndLoadMore($bundle, $resources=[], $previousRequest=null)
  {
    if($bundle instanceof Bundle) {
      // process also all entries from bundle
      $entries = $bundle->getEntries();
      $resources = array_merge($resources, $entries);
      // check for paginated results
      $next_request=$bundle->getNextRequest();
      if($next_request && $next_request!=$previousRequest) {
        $new_bundle = $this->getResource($next_request);
        $resources = $this->unzipBundleAndLoadMore($new_bundle, $resources, $next_request);
      }
    }
    return $resources;
  }

  /**
   * get entries for a category of mapping fields.
   * if a bundle is paginated load all pages.
   *
   * @param string $mrn
   * @param string $category FHIR categories like Demographics, Laboratory, Medications...
   * @param array $mapping [fields, minDate, maxDate]
   * @return AbstractResource[] list of resources
   */
  public function getEntries($mrn, $category, $mapping)
  {
    try {
      $notificationData = [
        'user_id' => $this->fhirTokenManager->getUserId(),
        'project_id' => $this->project_id,
        'mrn' => $mrn,
        'category' => $category,
        'mapping' => $mapping,
        'timestamp' => date_create()->format('Y-m-d H:i'),
        'status' => null,
        'entries' => [],
      ];

      $patient_id = $this->getPatientID($mrn);
      if(!$patient_id) {
        throw new \Exception(sprintf("Cannot find a patient ID for the MRN '%s'", $mrn), 404);
      };

      $this->fhirTokenManager->setPatientId($patient_id);
      $endpointFactory = $this->getEndpointFactory();
      $fields = @$mapping['fields'];
      $minDate = @$mapping['minDate'];
      $maxDate = @$mapping['maxDate'];
      $endpointVisitor = new RedcapEndpointVisitor($this, $patient_id, $fields, $minDate, $maxDate);
      $endpoint = $endpointFactory->makeEndpoint($category);
      $params = $endpoint->accept($endpointVisitor);
      $request = $endpoint->getSearchRequest($params);
      $bundle = $this->getResource($request);
      $entries = $this->unzipBundleAndLoadMore($bundle);
      $notificationData['entries'] =  $entries;
      return $entries;
    } catch (\Exception $e) {
      $errorMessage = sprintf('There was an error fetching \'%s\' for the patient \'%s\'.', $category, $mrn);
      $error = new Exception($errorMessage, $e->getCode(), $e);
      $this->addError($error);
      $notificationData['status'] =  $e->getCode();
    }finally {
      // notify listeners with data from the process
      $this->notify(self::NOTIFICATION_ENTRIES_RECEIVED, $notificationData);
    }
  }


  /**
   * helper function to fetch data
   * for REDCap CDIS enabled projects (CDP, CDM).
   * The provided FhirMetadataSource will help grouping
   * the data returned from the EHR system.
   * The FhirMetadataSource will also filter the results: if
   * the 'email' filed is not allowed due to project/system settings
   * it will not be returned.
   *
   * @param string $mrn
   * @param array $mapping_list
   * @param FhirMetadataSource $fhirMetadataSource
   * @return array
   */
  public function fetchData($mrn, $mapping_list=[], $fhirMetadataSource)
  {
    $groupedMapping = $this->groupMappingByCategory($fhirMetadataSource, $mapping_list);
    $groupedData = [];
    foreach ($groupedMapping as $category => $mapping) {
        $entries = $this->getEntries($mrn, $category, $mapping);
        $groupedData[$category] = $entries;
    }
    return $groupedData;
  }


  /**
   * return a list of exceptions
   *
   * @return Exception[]
   */
  public function getErrors()
  {
    return $this->errors;
  }

  /**
   * add an error
   *
   * @param Exception $exception
   * @return void
   */
  public function addError($exception)
  {
    /**
     * intercept error codes and set custom messages
     */
    // $lang['data_entry_400']
    // $lang['data_entry_401']
    $makeErrorObject = function(Exception $e) {
      switch ($code = $e->getCode()) {
        case 400:
          $explanation = 'Wrong format: one or more parameters are incorrect or missing.';
          break;
        case 401:
          $explanation = 'You are unauthorised. Please make sure to have a valid access token.';
          break;
        case 403:
          $explanation = 'Access is forbidden: either you do not have the right privileges or the data is protected.';
          break;
        case 404:
          $explanation = 'Nothing was found. Please check your parameters.';
          break;
        default:
          $explanation = '';
          break;
      }
      $message = $e->getMessage();
      $descriptiveException = empty($explanation) ? null : new SerializableException($explanation, $code);
      return new SerializableException($message, $code, $descriptiveException); // make a new exception with additional info (if applicable)
    };
    $error = $makeErrorObject($exception);
    $this->errors[] = $error;
    $this->notify(self::NOTIFICATION_ERROR, $error);
  }

  /**
   *
   * @return FhirVersionManager
   */
  public function getFhirVersionManager()
  {
    return $this->fhirVersionManager;
  }

  /**
   *
   * @return FhirTokenManager
   */
  public function getFhirTokenManager()
  {
    return $this->fhirTokenManager;
  }

  /**
   * Make a fetch request and get a FHIR resource.
   * Extract entries from a Bundle and makes calls for
   * more results if next_page is detected
   *
   * @param FhirRequest $request
   * @return AbstractResource
   */
  public function getResource($request)
  {
    try {
      $accessToken = $this->fhirTokenManager->getAccessToken();
      $response = $request->send($accessToken);
      // notify the response to subscibers
      $this->notify(self::NOTIFICATION_REQUEST_SENT, compact('response', 'request', 'accessToken'));
      $payload = json_decode($response, true);
      $resourceFactory = $this->fhirVersionManager->getResourceFactory();
      $resource = $resourceFactory->make($payload);
      return $resource;
    } catch (\Exception $error) {
      $this->notify(self::NOTIFICATION_REQUEST_ERROR, compact('error', 'request', 'accessToken'));
      throw $error;
    }
  }

  /**
   * get an endpoint factory based on the current FHIR version
   *
   * @return EndpointFactoryInterface
   */
  public function getEndpointFactory()
  {
    if(!$this->endpointFactory) {
      $factory = $this->fhirVersionManager->getEndpointFactory();
      if(!$factory) {
        $error = new \Exception(sprintf("No endpoint factory available for the FHIR version '%s'.", $this->fhirVersionManager->getVersion()), 400);
        $this->addError($error);
        return false;
      }
      $this->endpointFactory = $factory;
    }
    return $this->endpointFactory;
  }

  /**
   * group the mapping of a project
   * by category.
   * categories are listed in the class FhirCategory
   * 
   * @param FhirMetadataSource $fhirMetadataSource
   * @param array $fhir_fields
   * @return array
   */
  private function groupMappingByCategory($fhirMetadataSource, $fhir_fields)
  {
    
    /**
     * compare 2 dates and get the best choice based on a strategy
     * @param DateTime $date_a
     * @param DateTime $date_b
     * @param string $strategy min|max
     * @return DateTime|false
     */
    $getBestDate = function($date_a, $date_b, $strategy) {
      if(!$date_a && !$date_b) false;
      if(!$date_a) return $date_b;
      if(!$date_b) return $date_a;
      switch($strategy) {
        case('min'):
          $best = $date_a<$date_b ? $date_a : $date_b;
          break;
        case('max'):
          $best = $date_a>$date_b ? $date_a : $date_b;
          break;
        default:
          $best = false;
          break;
      }
      return $best;
    };
    /**
     * check if a metadata field has been disabled
     */
    $isMetadataFieldDisabled = function($metadata_array, $field_name) {
      return boolval(@$metadata_array[$field_name]['_disabled']);
    };
    $metadata_array = $fhirMetadataSource->getList();
    $groups = [];
    foreach ($fhir_fields as $data) {
      $field_name = @$data['field'];
      $disabled = $isMetadataFieldDisabled($metadata_array, $field_name);
      if($disabled) {
        continue; //skip disabled mappings (e.g. adverse events or emails)
      }
      $category = @$metadata_array[$field_name]['category'];
      if(!$category) continue;
      $groups[$category]['fields'][] = $field_name;
      if(!@$metadata_array[$field_name]['temporal']) continue; // not a temporal type of mapping; skip dates
      if($date_min = @$data['timestamp_min']) {
        $stored_date = @$groups[$category]['minDate']; // store a reference date for the entire category
        $groups[$category]['minDate'] = $getBestDate($stored_date, $date_min, 'min');
      }
      if($date_max = @$data['timestamp_max']) {
        $stored_date = @$groups[$category]['maxDate']; // store a reference date for the entire category
        $groups[$category]['maxDate'] = $getBestDate($stored_date, $date_max, 'max');
      }
    }

    return $groups;
  }


}