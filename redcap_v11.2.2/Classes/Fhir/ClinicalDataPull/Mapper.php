<?php

namespace Vanderbilt\REDCap\Classes\Fhir\ClinicalDataPull;

use CdpController;
use Logging;
use Project;
use Vanderbilt\REDCap\Classes\Fhir\ClinicalDataPull\AutoAdjudication\AutoAdjudicator;
use Vanderbilt\REDCap\Classes\Fhir\FhirMetadata\Decorators\FhirMetadataAdverseEventDecorator;
use Vanderbilt\REDCap\Classes\Fhir\FhirMetadata\Decorators\FhirMetadataEmailDecorator;
use Vanderbilt\REDCap\Classes\Fhir\FhirVersionManager;
use Vanderbilt\REDCap\Classes\Fhir\MappingHelper\FhirMappingHelper;

class Mapper
{
    /**
     * REDCap project instance
     *
     * @var Project
     */
    private $project;

    /**
     * ID of the project
     *
     * @var integer
     */
    private $project_id;

    /**
     * system configuration
     *
     * @var array
     */
    private $config;

    const MAPPING_TABLE = 'redcap_ddp_mapping';
    const CACHED_RECORDS_TABLE = 'redcap_ddp_records';

    const MAX_PREVIEW_FIELDS = 5;

    /**
     * DynamicDataPull instance
     *
     * @var \DynamicDataPull
     */
    private $ddp;

    /**
     * directory containing the exported CSV files
     *
     * @var string
     */
    private $downloads_directory;



    /**
     *
     * @param Project $project
     * @param array $config system configuration
     */
    public function __construct($project, $config)
    {
        $this->project = $project;
        $this->config = $config;
        $this->project_id = $project_id = $project->project_id;
        $realtime_webservice_type = $project->project['realtime_webservice_type'];
        $this->ddp = new \DynamicDataPull($project_id, $realtime_webservice_type);
        $this->setDownloadDirectory();
    }
    
    private function setDownloadDirectory()
    {
        $temp_dir = APP_PATH_TEMP ?: sys_get_temp_dir();
        $this->downloads_directory = preg_replace('#/+$#', '', $temp_dir);
    }

    /**
     * get the mappable fields based on the reatime webservice type (FHIR or CUSTOM)
     *
     * @return void
     */
    public function getMappableFields()
    {
        $realtime_webservice_type = strtoupper(@$this->project->project['realtime_webservice_type']);
        switch ($realtime_webservice_type) {
            case 'FHIR':
                $fields = $this->getFhirMappableFields();
                break;
            case 'CUSTOM':
                $fields = $this->getDdpMappableFields();
                break;
            default:
                $fields = [];
                break;
        }
        return $fields;
    }
    
    /**
     * get a list of mappable fields from the Dynamic Data Pull
     * metadata webservice.
     * The list is structured like this: [key => data][]
     *
     * @return array
     */
    public function getDdpMappableFields()
    {
        $metadata_url = @$this->config['realtime_webservice_url_metadata'];
        $http_options = [];
        $response = \HttpClient::request('POST', $metadata_url, $http_options);
        $body = $response->getBody();
        $fields = json_decode($body, $assoc=true);
        $fields_with_key = array_reduce($fields, function($accumulator, $field_data) {
            $map_object = [
                'field'       => $field_name = @$field_data['field'],
                'temporal'    => boolval(@$field_data['temporal']),
                'label'       => utf8_encode(@$field_data['label']),
                'description' => utf8_encode(@$field_data['description']),
                'category'    => @$field_data['category'],
                'subcategory' => @$field_data['subcategory'],
                'identifier'  => boolval(@$field_data['identifier'])
              ];
            $accumulator[$field_name] = $map_object;
            return $accumulator;
        }, []);
        return $fields_with_key;
    }

    /**
     * Get a list of FHIR data fields that can be mapped in REDCap.
     * Fields are sorted by category, subcategory and field
     *
     * @return array
     */
    public function getFhirMappableFields()
    {
        $fhirMetadataSource = $this->ddp->getFhirMetadataSource();
        return $fhirMetadataSource->getList();
    }

    /**
     * get fields and date fields for the project
     * fields are grouped by form name
     *
     * @return array ('fields', 'date_fields')
     */
    public function getProjectFields()
    {
        // check if field must be collected or skipped
        $shouldSkipField = function($field_name, $attributes) {
            // ignore SQL fields and Form Status fields 
            if($field_name==$attributes['form_name'].'_complete') return true;
            if($attributes['element_type']=='sql') return true;
            if($attributes['element_type']=='checkbox') return true;
            return false;
        };

        // check if the field is storing dates
        $isDateField = function($attributes) {
            if ($attributes['element_type'] != 'text') return false;
            $validation_type = $attributes['element_validation_type'];
            if(empty($validation_type)) return false;
            $date_validation_types = ['date', 'date_ymd', 'date_mdy', 'date_dmy'];
            if(in_array($validation_type, $date_validation_types)) return true;
            if(preg_match('/^datetime/', $validation_type)) return true;
            return false;
        };

        // check if field is identifier
        $isIdentifier = function($attributes) {
            return boolval($attributes['field_phi']);
        };

        $project_metadata = $this->project->metadata;

        $fields = []; // standard fields accumulator
        $date_fields = []; // date fields accumulator

        // loop through fields and collect them
        foreach($project_metadata as $field_name => $field) {
            // ignore SQL fields and Form Status fields 
            if($shouldSkipField($field_name, $field)) continue;

            $fields[] = $field;
            
            // also collect as date field if possible
            if($isDateField($field)) {
                $date_fields[] = $field;
            }
        }
        return [$fields, $date_fields];
    }

    /**
     * collect useful data about a project
     *
     * @return array
     */
    public function getProjectData()
    {
        $project = $this->project;
        
        $repeating_forms_events = $project->getRepeatingFormsEvents(); // list of repeating forms with custom label
        $data = [
            'longitudinal' => $project->longitudinal ?: false,
            'arms' => $project->events ?: [],
            'fhir_cdp_auto_adjudication_allowed' => AutoAdjudicator::isAllowed(),
            'fhir_cdp_auto_adjudication_enabled' => AutoAdjudicator::isEnabled($this->project_id),
            'realtime_webservice_type' => $project->project['realtime_webservice_type'],
            'realtime_webservice_offset_days' => $project->project['realtime_webservice_offset_days'],
            'realtime_webservice_offset_plusminus' => $project->project['realtime_webservice_offset_plusminus'],
            'events_forms' => $project->eventsForms, // list of instruments
            'metadata' => $project->metadata,
            'forms' => $project->forms,
            'events' => $project->eventInfo,
            'repeating_forms_events' => $repeating_forms_events, // data about fields
        ];
        return $data;
    }

    private function getSetupMappingPageLanguage($project)
    {
        $setRwsDescription = function($realtime_webservice_type, $lang, &$accumulator) {
            $ddp_description = @$lang['ws_37']; // CUSTOM
            $ddp_description_1 = @$lang['ws_13']; // CUSTOM
            $cdp_description = @$lang['ws_288']; // FHIR
            $cdp_description_1 = @$lang['ws_289']; // FHIR
            if($realtime_webservice_type==='CUSTOM') {
                $accumulator['rws_description'] = $ddp_description;
                $accumulator['rws_description_1'] = $ddp_description_1;
            }
            else {
                $accumulator['rws_description'] = $cdp_description;
                $accumulator['rws_description_1'] = $cdp_description_1;
            }
            return $accumulator;
        };

        $project_language = $project->project['project_language'];
        $lang = \Language::getLanguage($project_language);
        $translations = [
            'rws_description_2' => @$lang['ws_54'], // second part of the description (same for CUSTOM AND FHIR)
            'tell_me_more' => @$lang['global_58'],
            'settings_table_title' => @$lang['ws_117'],
            'preview_fields_title' => @$lang['ws_118'],
            'preview_fields_description' => @$lang['ws_119'],
            'preview_fields_select_label' => @$lang['ws_121'],
            'preview_fields_select_up_to' => @$lang['ws_122'],
            'preview_fields_add_another_field' =>   @$lang['cdp_preview_add_another_field'],
            'default_day_offset_title' => @$lang['ws_124'],
            'default_day_offset_description' => @$lang['ws_125'],
            'default_day_offset_label' => @$lang['ws_126'],
            'days' => @$lang['scheduling_25'],
            'min_max_days' => @$lang['ws_191'],
            'instant_adjudication_title' => @$lang['cdp_auto_adjudication_settings_title'],
            'instant_adjudication_description' => @$lang['cdp_auto_adjudication_settings_description'],
            'instant_adjudication_label' => @$lang['cdp_auto_adjudication_settings_label'],
            'instant_adjudication_total_selected_fields' => @$lang['cdp_total_selected_fields'], // must replace {{fields}} {{total_fields}}
            'instant_adjudication_enabled' => @$lang['cdp_instant_adjudication_enabled'],
            'instant_adjudication_disabled' => @$lang['cdp_instant_adjudication_disabled'],
            'instant_adjudication_disabled_description' => @$lang['cdp_instant_adjudication_disabled_description'],
            'instant_adjudication_enabled_text' => @$lang['cdp_instant_adjudication_enabled_text'],
            'instant_adjudication_disabled_text' => @$lang['cdp_instant_adjudication_disabled_text'],
            'find_more_sources_fields_to_map' => @$lang['ws_134'],
            'mapping_table_title' => @$lang['ws_130'],
            'mapping_table_header_external_source' => @$lang['cdp_setup_table_header_external_source'],
            'mapping_table_header_event' => @$lang['cdp_setup_table_header_event'],
            'mapping_table_header_field' => @$lang['cdp_setup_table_header_field'],
            'mapping_table_header_date' => @$lang['cdp_setup_table_header_date'],
            'mapping_table_header_preselect_strategy' => @$lang['cdp_setup_table_header_preselect_strategy'],
            'mapping_table_header_actions' => @$lang['cdp_setup_table_header_actions'],
            'source_identifier_field' => @$lang['ws_116'],
            // preselect strategies
            'preselect_none' => @$lang['database_mods_81'], // "none"
            'preselect_lowest' => @$lang['ws_15'], // "Lowest numerical value"
            'preselect_highest' => @$lang['ws_16'], // "Highest numerical value"
            'preselect_earliest' => @$lang['ws_17'], // "Earliest value (based on timestamp)"
            'preselect_latest' => @$lang['ws_18'], // "Latest value (based on timestamp)"
            'preselect_nearest' => @$lang['ws_194'], // "Nearest value (based on timestamp)"
            // edit/create mapping form
            'form_label_fhir_field' => @$lang['cdp_mapping_form_label_fhir_field'],
            'form_label_redcap_event' => @$lang['cdp_mapping_form_label_redcap_event'],
            'form_label_redcap_field' => @$lang['cdp_mapping_form_label_redcap_field'],
            'form_label_date_time' => @$lang['cdp_mapping_form_label_date_time'],
            'form_label_preselect_strategy' => @$lang['cdp_mapping_form_label_preselect_strategy'],
        ];
        // dynamically set the RWS description based on type (FHIR or CUSTOM)
        $realtime_webservice_type = @$project->project['realtime_webservice_type'];
        $translations = $setRwsDescription($realtime_webservice_type, $lang, $translations);

        return $translations;
    }

    public function getSettings()
    {
        $project_data = $this->getProjectData();
        // group standard fields and temporal fields
        list($project_fields, $project_temporal_fields) = $this->getProjectFields();
        $mapping = $this->getMappingList();
        $fhir_mappable_fields = $this->getMappableFields(); // picks FHIR or CUSTOM metadata
        $translations = $this->getSetupMappingPageLanguage($this->project);
        $preview_fields = $this->ddp->getPreviewFields();

        $mapping_helper_url = FhirMappingHelper::getLink($this->project_id);

        $settings = [
            'translations' => $translations,
            'project' => $project_data,
            'project_id' => $this->project_id,
            'fhir_source_name' =>  \DynamicDataPull::getSourceSystemName(),
            'fhir_fields' => $fhir_mappable_fields,
            'mapping' => $mapping,
            'project_fields' => $project_fields,
            'project_temporal_fields' => $project_temporal_fields,
            'day_offset_min' =>  \DynamicDataPull::DAY_OFFSET_MIN,
            'day_offset_max' =>  \DynamicDataPull::DAY_OFFSET_MAX,
            'preview_fields' => $preview_fields,
            'mapping_helper_url' => $mapping_helper_url,
        ];
        return $settings;
    }

    public function saveSettings($settings)
    {
        $preview_fields = @$settings['preview_fields'] ?: [];
        $fhir_cdp_auto_adjudication_enabled = @$settings['fhir_cdp_auto_adjudication_enabled'] ?: false;
        $realtime_webservice_offset_days = @$settings['realtime_webservice_offset_days'] ?: 7;
        $realtime_webservice_offset_plusminus = @$settings['realtime_webservice_offset_plusminus'] ?: '+-';
        try {
            db_query("SET AUTOCOMMIT=0");
            db_query("BEGIN");
            $offset_days_updated = $this->updateOffsetDays($realtime_webservice_offset_days, $realtime_webservice_offset_plusminus);
            $auto_adjudication_updated = $this->updateAutoAdjudication($fhir_cdp_auto_adjudication_enabled);
            $preview_fields_updated = $this->updatePreviewFields($preview_fields);
            db_query("COMMIT");
            return compact('offset_days_updated', 'auto_adjudication_updated', 'preview_fields_updated');
        } catch (\Exception $e) {
            db_query("ROLLBACK");
            throw $e;
        }finally {
            db_query("SET AUTOCOMMIT=1");
        }
    }

    private function updatePreviewFields($fields=[])
    {
        /* $delete_preview_fields = function($project_id) {
            $query_string = "DELETE FROM redcap_ddp_preview_fields WHERE project_id = ".$this->project_id;
            $result = db_query($query_string);
            return $result;
        }; */
        $upsert_preview_fields = function($project_id, $fields) {
            // get an associative array with DB valid keys and default values (null)
            $getFieldsWithKeys = function($fields) {
                $total_fields = self::MAX_PREVIEW_FIELDS;
                // create an array of field names valid for the DB structure
                $keys = array_map(function($number) {
                    return "field{$number}";
                }, range(1,$total_fields));
                // create an associative array with default preview values
                $keys_values = [];
                for ($i=0; $i < count($keys); $i++) {
                    $key = $keys[$i];
                    $value = @$fields[$i] ?: null;
                    $keys_values[$key] = $value;
                }
                return $keys_values;
            };
            $fields_with_keys = $getFieldsWithKeys($fields);

            $db_keys = array_map(function($item) { return "`".db_escape($item)."`";}, array_keys($fields_with_keys));
            array_unshift($db_keys, '`project_id`'); // add project_id key
            
            $db_values = array_map(function($item) { return  checkNull($item);}, $fields_with_keys);
            array_unshift($db_values, $project_id); // add project_id value

            $db_associative = array_map(
                function ($value, $key) { return sprintf("%s=%s", $key, $value); },
                $db_values,
                $db_keys
            ); // used when updating

            $query_string = sprintf(
                "INSERT INTO redcap_ddp_preview_fields
                (%s) 
                values (%s)
                ON DUPLICATE KEY UPDATE %s",
                implode(', ', $db_keys),
                implode(', ', $db_values),
                implode(', ', $db_associative)
            );
            $result = db_query($query_string);
            if(!$result) {
                $message = sprintf("Error storing preview fields in the database. Fields: %s", implode(', ', $fields));
                throw new \Exception($message, 400);
            }
            $affected_rows = db_affected_rows();
            db_free_result($result);
            return $affected_rows;
        };
        return $upsert_preview_fields($this->project_id, $fields);
    }

    private function updateAutoAdjudication($enabled)
    {
        $enabled_int = intval(boolval($enabled));
        // Add day offset defaults to redcap_projects
        $query_string = sprintf(
            "UPDATE redcap_projects
            SET `fhir_cdp_auto_adjudication_enabled`=%u
            WHERE project_id=%u",
            $enabled_int,
            $this->project_id)
        ;
        $result = db_query($query_string);
        if(!$result) {
            $message = sprintf("Error storing auto-adjudication setting in the database. Setting: %s", $enabled);
            throw new \Exception($message, 400);
        }
        $affected_rows = db_affected_rows();
        db_free_result($result);
        return $affected_rows;
    }
    /**
     * update offset days settings on the database
     *
     * @param integer $realtime_webservice_offset_days
     * @param string $realtime_webservice_offset_plusminus
     * @return void
     */
    private function updateOffsetDays($days, $plusminus)
    {
        // Add day offset defaults to redcap_projects
        $query_string = sprintf(
            "UPDATE redcap_projects SET realtime_webservice_offset_days='%s',
            realtime_webservice_offset_plusminus='%s'
            WHERE project_id=%u",
            db_escape($days),
            db_escape($plusminus),
            $this->project_id)
        ;
        $result = db_query($query_string);
        if(!$result) {
            $message = sprintf("Error storing 'offset days' settings in the database. Offset: '%s' - PlusMinus: '%s'", $days, $plusminus);
            throw new \Exception($message, 400);
        }
        $affected_rows = db_affected_rows();
        db_free_result($result);
        return $affected_rows;
    }

    /**
     * Undocumented function
     *
     * @param array $mapping
     * @return void
     */
    public function saveMapping($mapping)
    {
        $existing_mapping = $this->getMappingList(); // check if a mapping isse
        $existing_mapping_ids = array_column($existing_mapping, 'map_id'); // get list of map_id
        $processed = [];

        foreach ($mapping as $mapping_config) {
            $map_id = @$mapping_config['map_id'] ?: null;
            $status = @$mapping_config['status'];
            $index = array_search($map_id, $existing_mapping_ids);
            if($index===false) {
                $new_id = $this->insertMapping($mapping_config);
                $processed[$new_id] = "created";
            }else {
                if(is_nan($map_id)) {
                    $error_message = "To update or delete a mapping a numeric map_id is needed. Provided: {$map_id}";
                    throw new \Exception($error_message, 400);
                }
                if($status=='deleted') {
                    $affected_rows = $this->deleteMapping($map_id);
                    $processed[$map_id] = "deleted. Affected rows: {$affected_rows}";
                }else {
                    $existing = $this->getMapping($map_id);
                    $diff = array_diff_assoc($existing, $mapping_config); // compare with existing data
                    if(empty($diff)) {
                        $processed[$map_id] = "not changed.";
                    }else {
                        // update only if something changed
                        $affected_rows = $this->updateMapping($mapping_config);
                        $processed[$map_id] = "updated. Affected rows: {$affected_rows}";
                    }
                }
                array_splice($existing_mapping_ids, $index, 1); // remove processed $id
            }
        }
        // queue all records after the mapping is updated
        $this->queueDdpRecords();

        return $processed;
    }

    /**
     * mark records in the ddp_records table as QUEUED
     * so REDCap will fetch data for them.
     * this will allow to fetch new data for existing records
     * if a user add a mapping to the configuration
     *
     * @return int
     */
    private function queueDdpRecords()
    {
        $this->project_id;
        $query_string = sprintf(
            "UPDATE %s SET fetch_status='QUEUED' WHERE project_id=%u",
            self::CACHED_RECORDS_TABLE, $this->project_id
        );
        $result = db_query($query_string);
        if(!$result) {
            throw new \Exception(sprintf("Error updating the fetch status in %s",self::CACHED_RECORDS_TABLE), 1);
        }
        $affected_rows = db_affected_rows();
        return $affected_rows;
    }

    /**
     * get mapped fields from the database
     *
     * @return array (field_name=>mapping_data)
     */
    public function getMappingList()
    {
        $query_string = sprintf(
            "SELECT * FROM %s WHERE project_id = %u",
            self::MAPPING_TABLE,
            $this->project_id
        );
        $result = db_query($query_string);
        $rows = [];
        while ($row = db_fetch_assoc($result))
        {
            $rows[] = $row;
        }
        // Return the array of field mappings
        return $rows;
    }

    /**
     * get a mapping using the ID
     *
     * @param int $map_id
     * @return array|false
     */
    public function getMapping($map_id)
    {
        $query_string = sprintf(
            "SELECT * FROM %s WHERE project_id = %u AND map_id = %u",
            self::MAPPING_TABLE,
            $this->project_id,
            db_escape($map_id)
        );
        $result = db_query($query_string);
        if ($row = db_fetch_assoc($result))
        {
            return $row;
        }
        return false;
    }

    /**
     * insert a mapping into the database
     *
     * @param array $config mapping configuration
     * @return int|string inserted ID
     */
    private function insertMapping($config)
    {
        $query_string = sprintf(
            "INSERT INTO `%s`
            (project_id, event_id, field_name, external_source_field_name, is_record_identifier, temporal_field, preselect)
            VALUES (%u, %u, '%s', '%s', %s, %s, %s)",
            self::MAPPING_TABLE,
            $this->project_id, db_escape(@$config['event_id']), db_escape(@$config['field_name']),
            db_escape(@$config['external_source_field_name']), checkNull(@$config['is_record_identifier']),
            checkNull(@$config['temporal_field']), checkNull(@$config['preselect'])
        );
        $result = db_query($query_string);
        if(!$result) {
            $error_message = sprintf("Error creating a new mapping.\nProvided settings:\n%s ", json_encode($config, JSON_PRETTY_PRINT));
            throw new \Exception($error_message, 400);
        }
        $new_id = db_insert_id();
        Logging::logEvent($query_string, self::MAPPING_TABLE, "MANAGE", $new_id, "", "Mapping configuration created");

        db_free_result($result);
        return $new_id;
    }

    /**
     * update an existing mapping on the database
     *
     * @param array $config mapping configuration
     * @return int updated fields
     */
    private function updateMapping($config)
    {
        $map_id = @$config['map_id'];
        $query_string = sprintf(
            "UPDATE `%s` SET
                `project_id` = %u,
                `event_id` = %u,
                `field_name` = '%s',
                `external_source_field_name` = '%s',
                `is_record_identifier` = %s,
                `temporal_field` = %s,
                `preselect` = %s
            WHERE
                `project_id` = %u
                AND `map_id` = %u",
            self::MAPPING_TABLE,
            $this->project_id, db_escape(@$config['event_id']), db_escape(@$config['field_name']),
            db_escape(@$config['external_source_field_name']), checkNull(@$config['is_record_identifier']),
            checkNull(@$config['temporal_field']), checkNull(@$config['preselect']),
            $this->project_id, db_escape($map_id)
        );
        $result = db_query($query_string);
        if(!$result) {
            $error_message = sprintf("Error updating the mapping with ID '%s'.\nProvided settings:\n%s", $map_id, json_encode($config, JSON_PRETTY_PRINT));
            throw new \Exception($error_message, 400);
        }
        
        Logging::logEvent($query_string, self::MAPPING_TABLE, "MANAGE", $map_id, "", "Mapping configuration updated");
        $affected_rows = db_affected_rows();
        db_free_result($result);
        return $affected_rows;
    }

    /**
     * delete a mapping from the database
     *
     * @param int $map_id
     * @return void
     */
    private function deleteMapping($map_id)
    {
        if(is_nan($map_id)) return;
        $query_string = sprintf(
            "DELETE FROM `%s`
            WHERE
            project_id = %u
            AND map_id = %u",
            self::MAPPING_TABLE,
            $this->project_id, $map_id
        );
        $result = db_query($query_string);
        if(!$result) {
            $error_message =sprintf("Error deleting the mapping with ID '%s'.", $map_id);
            throw new \Exception($error_message, 400);
        }
        Logging::logEvent($query_string, self::MAPPING_TABLE, "MANAGE", $map_id, "", "Mapping configuration deleted");
        $affected_rows = db_affected_rows();
        db_free_result($result);
        return $affected_rows;
    }

    /**
     * delete all mapping for a project
     *
     * @return int deleted rows
     */
    private function deleteAllMapping()
    {
        $query_string = sprintf(
            "DELETE FROM %s WHERE project_id=%u",
            self::MAPPING_TABLE, $this->project_id
        );
        $result = db_query($query_string);
        if(!$result) {
            $error_message =sprintf("Error deleting the mapping from project  '%u'.", $this->project_id);
            throw new \Exception($error_message, 400);
        }
        $affected_rows = db_affected_rows();
        db_free_result($result);
        return $affected_rows;
    }

    public function importMapping($file_path)
    {
        $project_id = $this->project_id;

        $hasRecordIdentifierMapping = function($mapping) {
            $identifiers = array_column($mapping, 'is_record_identifier');
            $has_identifier = array_search('1', $identifiers);
            if($has_identifier>=0) return true;
            else throw new \Exception("A mapping with a record identifier is mandatory; none provided", 400);
        };

        $convertEventNameToID = function(&$config) use($project_id) {
            $event_name = @$config['event_name'];
            $event_id = \Event::getEventIdByName($project_id, $event_name);
            if(!$event_id) throw new \Exception("An event with name '{$event_name}' was not found in the current project.", 1);
            $config['event_id'] = $event_id;
        };


        try {
            $csv = \FileManager::readCSV($file_path);
            $mapping = \FileManager::csvToAssociativeArray($csv);
            $hasRecordIdentifierMapping($mapping);
            
            db_query("BEGIN");
            db_query("SET AUTOCOMMIT=0");
            $this->deleteAllMapping();
            $id_list = [];
            foreach ($mapping as $config) {
                $convertEventNameToID($config);
                $id_list[] = $this->insertMapping($config);
            }
            db_query("COMMIT");
            $response = ['imported' => $id_list];
            return $response;
        } catch (\Exception $e) {
            db_query("ROLLBACK");
            throw $e;
        }finally {
            db_query("SET AUTOCOMMIT=1");
        }
    }

    /**
     * download the file from the downloads (temp) directory
     *
     * @param string $file_name
     * @return void
     */
    public function download($file_name)
    {
        $file_path = implode(DIRECTORY_SEPARATOR, [$this->downloads_directory, $file_name]);
        if(!file_exists($file_path)) throw new \Exception("Error file '{$file_name}' not found", 400);
        
        $quoted = sprintf('"%s"', addcslashes(basename($file_path), '"\\'));
        $size   = filesize($file_path);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $quoted); 
        header('Content-Transfer-Encoding: binary');
        header('Connection: Keep-Alive');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . $size);
        readfile($file_path);
    }

    private function getDownloadURL($file_name)
    {
        $data = [
            'pid' => $this->project_id,
            'route' => (CdpController::class).":download",
            'file_name' => $file_name
        ];
        $URL = APP_PATH_WEBROOT_FULL."redcap_v".REDCAP_VERSION."/?".http_build_query($data);
        return $URL;
    }

    /**
     * create a CSV and store it in a temp directory
     * for download
     *
     * @return string the name of the file
     */
    public function exportMapping()
    {
        $getFileName = function($project) {
            $project_name = @$project->project['project_name'];
            $timestamp = date('YmdHis');
            $file_name = "{$timestamp}_{$project_name}-cdp-mapping.csv";
            return $file_name;
        };
        $translateEventID = function($config) {
            $event_id = @$config['event_id'];
            $event_name = \Event::getEventNameById($this->project_id, $event_id);
            $config['event_name'] = $event_name;
            unset($config['event_id']);
            return $config;
        };
        $filterKeys = function($config) {
            $allowed_keys = [
                'external_source_field_name',
                'event_name',
                'field_name',
                'is_record_identifier',
                'temporal_field',
                'preselect'
            ];
            $config = array_filter($config, function($key) use($allowed_keys) {
                return in_array($key, $allowed_keys);
            }, ARRAY_FILTER_USE_KEY);
            return $config;
        };


        $mapping = $this->getMappingList();
        foreach ($mapping as &$config) {
            $config = $translateEventID($config);
            $config = $filterKeys($config);
        }
        
        $csv = \FileManager::getCSV($mapping);
        // \FileManager::exportCSV($mapping, $file_name);
        $csv_with_BOM = addBOMtoUTF8($csv);
        $file_name = $getFileName($this->project);
        $file_path = implode(DIRECTORY_SEPARATOR, [$this->downloads_directory, $file_name]);

        $result = file_put_contents($file_path, $csv_with_BOM);
        if($result==false) throw new \Exception("Error saving the CSV file", 400);
        
        $URL = $this->getDownloadURL($file_name);
        return $URL;
    }
}
