<?php

namespace Vanderbilt\REDCap\Classes\Fhir\ClinicalDataPull\AutoAdjudication;

use DateTime;
use DynamicDataPull;
use JsonSerializable;
use Vanderbilt\REDCap\Classes\Fhir\Utility\ProjectProxy;
use Vanderbilt\REDCap\Classes\Fhir\Utility\ProjectValidationTypes\DateTimeValidation;

/**
 * modal for the record_data element tha contains
 * - redcap_ddp_records
 * - reddap_ddp_records_data
 * - redcap_ddp_mapping
 * 
 * @property integer $md_id
 * @property integer $map_id
 * @property integer $mr_id
 * @property string $source_timestamp
 * @property string $source_value
 * @property string $source_value2
 * @property boolean $adjudicated
 * @property boolean $exclude
 * @property string $record
 * @property string $external_source_field_name
 * @property boolean $is_record_identifier
 * @property integer $project_id
 * @property integer $event_id
 * @property string $field_name
 * @property string $temporal_field
 * @property string $preselect
 */
class RecordData implements JsonSerializable
{



  /**
   * format of the timestamp in redcap_ddp_records_data 
   */
  const SOURCE_DATE_FORMAT = 'Y-m-d H:i:s';

  /**
   * allowed field names
   *
   * @var array
   */
  private $fields = [
    'md_id',
    'map_id',
    'mr_id',
    'source_timestamp',
    'source_value',
    'source_value2',
    'adjudicated',
    'exclude',
    'record',
    'external_source_field_name',
    'is_record_identifier',
    'project_id',
    'event_id',
    'field_name',
    'temporal_field',
    'preselect',
  ];

  /**
   * caonfig values from the constructor
   *
   * @var array
   */
  private $config = [];


  /**
   *
   * @var ProjectProxy
   */
  private $project;

  /**
   * placeholder for the decrypted value of the data source
   *
   * @var mixed
   */
  private $value;

  /**
   * date object of the temporal field
   * based on the validation rules of the field
   * in the project
   *
   * @var DateTime[]
   */
  private $temporal_field_date_time = [];


  
  /**
   * date object of the source data
   *
   * @var DateTime
   */
  private $source_date_time;

  /**
   * Undocumented function
   *
   * @param ProjectProxy $project
   * @param array $config
   */
  public function __construct($project, $config)
  {
    $this->project = $project;
    $this->config = $config;
  }

  public function getID()
  {
    return $this->md_id;
  }

  /**
   * transform the timestamp into a DateTime object
   *
   * @return DateTime|null
   */
  public function getSourceDateTime()
  {
    if(!isset($this->source_date_time)) {
      $timestamp = $this->source_timestamp;
      if(empty($timestamp)) return;
      $this->source_date_time = DateTime::createFromFormat(self::SOURCE_DATE_FORMAT, $timestamp);
    }
    return $this->source_date_time;
  }

  /**
   * decrypt the value stored in source_value2
   *
   * @return string
   */
  public function getValue()
  {
    if(!isset($this->value)) {
      $use_mcrypt = $this->source_value2=='';
      $encrypted_data = $use_mcrypt ? $this->source_value : $this->source_value2;
      $this->value = decrypt($encrypted_data, DynamicDataPull::DDP_ENCRYPTION_KEY, $use_mcrypt);
    }
    return $this->value;
  }

  /**
   * get list of instance number where the source data
   * can be saved
   * - for regular data refer to the target field_name
   * - for temporal data refer to the target temporal_field
   *
   * @return array
   */
  public function getTargetInstances()
  {
    $temporal_field = $this->temporal_field;
    $event_id = $this->event_id;
    $record_id = $this->record;
    $field_name = $this->field_name;
    if(empty($temporal_field)) {
      $instances = $this->project->getFieldValue($record_id, $event_id, $field_name);
      if(empty($instances)) $instances[1] = ''; // add an empty spot if none available
    }else {
      $instances = $this->project->getFieldValue($record_id, $event_id, $temporal_field);
      foreach ($instances as $instance => $value) {
        $is_in_offset = $this->isInstanceInOffsetDays($instance);
        if(!$is_in_offset) unset($instances[$instance]); // remove instances not in offset days
      }
    }
    return array_keys($instances);
  }

  private function isInstanceInOffsetDays($instance)
  { 
    $getDateRange = function($date_time) {
      if(!($date_time instanceof DateTime)) $date_range = false;
      else $date_range = $this->project->getFhirOffsetDaysRange($date_time);
      return $date_range;
    };
    $isDateinRange = function($date_range) {
      $from = @$date_range['from'];
      $to = @$date_range['to'];
      if(!($from instanceof DateTime) || !($to instanceof DateTime)) return false;
      $source_date_time = $this->getSourceDateTime();
      return $source_date_time<=$to && $source_date_time>=$from;
    };

    $reference_date_time = $this->getTemporalFieldDateTime($instance);
    $date_range = $getDateRange($reference_date_time);
    $in_range = $isDateinRange($date_range);
    return $in_range;
  }

  /**
   * get the DateTime object 
   *
   * @param int $instance
   * @return DateTime|false
   */
  public function getTemporalFieldDateTime($instance)
  {
    if(!isset($this->temporal_field_date_time[$instance])) {
      $record_id = $this->record;
      $event_id = $this->event_id;
      $field_name = $this->temporal_field;
      $validation_type = $this->project->getFieldValidation($field_name);
      $result = $this->project->getFieldValue($record_id, $event_id, $field_name, $instance);
      $timestamp = current($result);
      if(empty($timestamp)) $this->temporal_field_date_time[$instance] = false;
      else {
        $date_format = DateTimeValidation::getDateFormatFromRedcapValidation($validation_type);
        $this->temporal_field_date_time[$instance] = DateTime::createFromFormat($date_format, $timestamp);
      }
    }
    return $this->temporal_field_date_time[$instance];
  }

  public function __get($name)
  {
    if(in_array($name, $this->fields)) return @$this->config[$name];
  }

  public function __set($name, $value)
  {
    if(in_array($name, $this->fields)) $this->config[$name] = $value;
  }

  public function jsonSerialize()
  {
    $data = [
      'md_id' => $this->md_id,
      'map_id' => $this->map_id,
      'mr_id' => $this->mr_id,
      'source_timestamp' => $this->source_timestamp,
      'source_value' => $this->source_value,
      'source_value2' => $this->source_value2,
      'adjudicated' => $this->adjudicated,
      'exclude' => $this->exclude,
      'record' => $this->record,
      'external_source_field_name' => $this->external_source_field_name,
      'is_record_identifier' => $this->is_record_identifier,
      'project_id' => $this->project_id,
      'event_id' => $this->event_id,
      'field_name' => $this->field_name,
      'temporal_field' => $this->temporal_field,
      'preselect' => $this->preselect,
      'value' => $this->getValue(),
      'source_date_time' => $this->getSourceDateTime(),
    ];
    return $data;
  }


}