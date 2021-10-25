<?php
namespace Vanderbilt\REDCap\Classes\Fhir;

use SplFileObject;
use Vanderbilt\REDCap\Classes\Fhir\FhirVersionManager;

class FhirMetadataManager
{
  /**
   *  FHIR version code used in REDCap
   * 
   * @var string
   */
  private $fhirCode;

  const FILE_NAME_DSTU2 = 'redcap_fhir_metadata_DSTU2.csv';
  const FILE_NAME_R4 = 'redcap_fhir_metadata_R4.csv';

  private $mapped_objects = [];

  /**
   * manage FHIR metadata:
   * the list of fields will be used to fetch
   * and map FHIR resources from an EHR system
   *
   * @param string $fhirCode a valid FHIR code as specified in FhirVersionManager
   */
  public function __construct($fhirCode)
  {
    $this->fhirCode = $fhirCode;    
  }

  /**
   * get a different metadata file based on
   * the specified FHIR code version (DSTU2, R4)
   *
   * @param string $fhirCode
   * @throws Exception if an invalid FHIR code is specified
   * @return string
   */
  private function getMetadataFilePath($fhirCode)
  {
    $baseMetadataFilePath = realpath(APP_PATH_DOCROOT) . "/Resources/misc/";
    switch ($fhirCode) {
      case FhirVersionManager::FHIR_DSTU2:
        $path = $baseMetadataFilePath.self::FILE_NAME_DSTU2;
        break;
      case FhirVersionManager::FHIR_R4:
        $path = $baseMetadataFilePath.self::FILE_NAME_R4;
        break;
      default:
        throw new \Exception(sprintf("Error: unable to find a metadata CSV file for the FHIR version '%s'", $fhirCode), 1);
        break;
    }
    return $path;
  }

  private function readMetadataFile($filePath)
  {
    $readLine = function() {};
    if(!file_exists($filePath)) throw new \Exception(sprintf("Error: unable to find the metadata file at path '%s'", $filePath), 1);
    $file = new SplFileObject($filePath, $open_mode='r');
    $firstLine = $file->fgetcsv($delimiter=',',$enclosure='"', $escape='\\');
    $metadata = [];
    while(!$file->eof()) {
      list($field, $label, $description, $temporal, $category, $subcategory, $identifier) = $file->fgetcsv($delimiter, $enclosure, $escape);
      $metadata[] = compact('field','label','description','temporal','category','subcategory','identifier');
    }
    return $metadata;
  }


  /**
   * order and normalize the values in the metadata array
   *
   * @return array
   */
  private function processMetadataFile()
  {
    /**
     * helper function to order fields by category,
     * subcategory,field name
     */
    $order_fields = function($fields) {
      array_multisort(
          array_column($fields, 'category'), SORT_ASC,
          array_column($fields, 'subcategory'), SORT_ASC,
          array_column($fields, 'field'), SORT_ASC,
          $fields
      );
      return $fields;
    };

    /**
     * apply filters on the list of available fields
     * based on REDCap settings
     */
    $applyRedcapFilters = function(&$metadata_array) {
      $systemConfig = \System::getConfigVals();
      $fhir_include_email_address = boolval(@$systemConfig['fhir_include_email_address']);

      $projectConfig = \Project::getProjectVals();
      $fhir_include_email_address_project = boolval(@$projectConfig['fhir_include_email_address_project']);
      if (!$fhir_include_email_address || !$fhir_include_email_address_project) {
        unset($metadata_array['email']);
        unset($metadata_array['email-2']);
        unset($metadata_array['email-3']);
      }

      $irbNumber = @$projectConfig['project_irb_number'];
      $purpose = intval(@$projectConfig['purpose']);
      if($purpose!==2 || !$irbNumber) {
        //do something with adverse event
      }
    };

    /**
     * process a row of metadata mapping to get a metadata object
     */
    $reduceMetadata = function($accumulator, $row) {
      $map_object = [
        'field'       => $field = @$row['field'],
        'temporal'    => boolval(@$row['temporal']),
        'label'       => utf8_encode(@$row['label']),
        'description' => utf8_encode(@$row['description']),
        'category'    => @$row['category'],
        'subcategory' => @$row['subcategory'],
        'identifier'  => boolval(@$row['identifier'])
      ];
      if ($map_object['identifier']) {
          // Always set the source id field's cat and subcat to blank so that it's viewed separate from the other fields
          $map_object['category'] = $map_object['subcategory'] = '';
      }
      $accumulator[$field] = $map_object;
      return $accumulator;
    };
    $filePath = $this->getMetadataFilePath($this->fhirCode);
    $fhir_metadata = $this->readMetadataFile($filePath);
    
    $data = array_reduce($fhir_metadata, $reduceMetadata, []);
    $applyRedcapFilters($data);
    $ordered = $order_fields($data);
    return $ordered;
  }

  /**
   * get a list of FHIR mapping objects
   *
   * @return array
   */
  public function getList()
  {
    if(!$this->mapped_objects) {
      $this->mapped_objects = $this->processMetadataFile();
    }
    return $this->mapped_objects;
  }

  /**
   * get the available mapping fields
   * grouped by category/subcategory.
   * use the internal list or a custom one provided
   * as argument
   *
   * @return array
   */
  public function getGroups($fields=null)
  {
    $fields = $fields ?: $this->getList();
    $groups = [];
    foreach ($fields as $field) {
        $category = @$field['category'];
        if(empty($category)) {
            // this is for ID field (no category or subcategory)
            $groups[] = $field;
            continue;
        }
        // priority to sub categories
        if($sub_category = @$field['subcategory']) $groups[$category][$sub_category][] = $field;
        else $groups[$category][] = $field;
    }
    return $groups;
  }

  /**
   * group the mapping of a project
   * by category.
   * categories are listed in the class FhirCategory
   * 
   * @param array $fhir_fields
   * @return array
   */
  public function groupMappingByCategory($fhir_fields)
  {
    /**
     * get a DateTime from a string
     * @param string $date_string
     * @return DateTime|false
     */
    /* $getDate = function($date_string) {
      $date = \DateTime::createFromFormat('Y-m-d H:i:s', $date_string);
      return $date;
    }; */
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
    $metadata_array = $this->getList();
    $groups = [];
    foreach ($fhir_fields as $data) {
      $field_name = @$data['field'];
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