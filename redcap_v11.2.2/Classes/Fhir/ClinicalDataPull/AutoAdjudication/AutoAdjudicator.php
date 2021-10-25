<?php

namespace Vanderbilt\REDCap\Classes\Fhir\ClinicalDataPull\AutoAdjudication;

use DateTime;
use DynamicDataPull;
use Logging;
use Renderer;
use User;
use Vanderbilt\REDCap\Classes\Fhir\FhirStats\FhirStatsCollector;
use Vanderbilt\REDCap\Classes\Fhir\Utility\ProjectProxy;
use Vanderbilt\REDCap\Classes\Queue\Queue;

class AutoAdjudicator
{
    /**
     * table names
     */
    const AUTO_ADJUDICATION_LOG_TABLE_TYPE = 'CDP_AUTO_ADJUDICATION';    
    const CACHED_RECORDS_DATA_TABLE = 'redcap_ddp_records_data';
    const CACHED_RECORDS_TABLE = 'redcap_ddp_records';
    const DDP_MAPPING_TABLE = 'redcap_ddp_mapping';

    /**
     * format of the timestamp in redcap_ddp_records_data 
     */
    const SOURCE_DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * define the maximum number of records to get metedata
     * for when using getDdpRecordsDataStats
     */
    const RECORDS_METADATA_CHUNK_SIZE = 100;

    /**
    * username of current user
    *
    * @var string
    */
    private $user_id;

    /**
    * project ID
    *
    * @var int
    */
    private $project_id;

    /**
     * current project
     *
     * @var ProjectProxy
     */
    private $project;
    
    /**
    * Auto Adjudicate data in CDP projects
    *
    * @param int $project_id
    */
    public function __construct($project_id, $user_id=false)
    {
        $this->user_id = $user_id;
        $this->project_id = $project_id;
        $this->project = new ProjectProxy($project_id);

    }
    
    public static function getLogsForProject($project_id, $limit=0, $start=0)
    {
        $log_table = \Logging::getLogEventTable($project_id);
        $query_string = sprintf("SELECT * FROM %s WHERE object_type='%s' LIMIT %u,%u", $log_table, self::AUTO_ADJUDICATION_LOG_TABLE_TYPE, $limit, $start);
        
        $result = db_query($query_string);
        if(!$result) {
            $message = sprintf("There was a problem retrieving the logs for project ID %s", $project_id);
            throw new \Exception($message, 400);
        }
        $rows = [];
        while($row = db_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * process every record ID and adjudicate data
     *
     * @param boolean $background wheter to start the process in background or not
     * @return mixed
     */
    public function adjudicateCachedRecords($background=false, $send_feedback=false)
    {

        $processRecords = function($pid, $user_id, $records, $send_feedback=false) {
            global $project_id;
            $project_id = $pid; // needed for logging
            $auto_adjudicator = new AutoAdjudicator($project_id, $user_id);
            $errors = []; // list of errors
            $total = 0; //total records processed
            $successful = 0; // total successful records processed
            $excluded = 0; // total excluded values
            $adjudicated = 0; // total adjudicated values
            foreach ($records as $record_id) {
                $response = $auto_adjudicator->adjudicateCachedRecord($record_id);
                $excluded += intval(@$response['excluded']) ?: 0;
                $adjudicated += intval(@$response['adjudicated']) ?: 0;
                $total++;
                if(@$response['has_errors']==false) {
                    $successful++;
                }else {
                    $errors[$record_id] = @$response['errors'];
                }
                // $results[$record_id] = $response;
            }
            $response = [
                'total records' => $total,
                'successful records' => $successful,
                'adjudicated values' => $adjudicated,
                'excluded values' => $excluded,
            ];
            if($send_feedback) {
                $auto_adjudicator->sendFeedback($response, $errors);
            };
            return $response;
        };
        
        $ddp_records_stats = $this->getDdpRecordsMetadata();
        $record_data = @$ddp_records_stats['record_data'] ?: [];
        $records = array_column($record_data, 'record'); // extract record IDs
        if($background==true) {
            $project_id = $this->project_id;
            $user_id = $this->user_id;
            $closure = function() use($project_id, $user_id, $records, $processRecords, $send_feedback) {
                return $processRecords($project_id, $user_id, $records, $send_feedback);
            };
            $queue_key = "AutoAdjudication_{$project_id}";
            Queue::addMessage($queue_key, $closure);
            $response = [
                'success'=>true,
                'message'=>'auto-adjudication added to background queue.'
            ];
            return $response;
        }else {
            return $processRecords($this->project_id, $this->user_id, $records, $send_feedback);
        }
    }

    public function sendFeedback($data, $errors) {
        $getImageAsDataUri = function($image)
        {
            $type = pathinfo($image, PATHINFO_EXTENSION);
            $data = file_get_contents($image);
            $dataUri = 'data:image/' . $type . ';base64,' . base64_encode($data);
            return $dataUri;
        };
        $redcap_logo_path = APP_PATH_DOCROOT.'Resources/images/redcap-logo-large.png';
        // $redcap_image_uri = $getImageAsDataUri($redcap_logo_path);

        $lang = $this->project->getLanguage();
        $system_config = \System::getConfigVals();

        $user_id = $this->user_id;
        $project_id = $this->project_id;
        $project = new \Project($project_id);
        $project_creator = @$project->project['created_by'];

        $blade = Renderer::getBlade();
        $blade->share('project_id', $project_id);
        // $blade->share('redcap_image_uri', $redcap_image_uri);
        $blade->share('lang', $lang);

        $html = $blade->run('cdp.auto-adjudication.adjudication-complete-email', compact('data', 'errors'));
        $user_info = User::getUserInfo($user_id);

        $to = @$user_info['username'];
        $title = "Instant Adjudication Completed";

        \Messenger::createNewConversation($title, $msg=$html, $from=$project_creator, $users=$to, $project_id);
    }

    /**
     * randomly throw an exception
     * the frequency depends on the specified percentage
     *
     * @param integer $percentage
     * @throws \Exception
     * @return void
     */
    private function getRandomError($percentage=80) {
        $random = rand(0,100);
        if($random>$percentage) {
            $error_messages = [
                'validation error',
                'something bad happened',
                'something REALLY bad happened',
                'timeout error',
                'bad data format',
                'unexpected error',
                'something REALLY bad happened',
                'connection error',
            ];
            $error_index = rand(0, count($error_messages)-1);
            $message = $error_messages[$error_index];
            throw new \Exception($message, 400);
        }
    }

    /**
     * adjudicate data for a record
     * - retrieve cahed data from the database
     * - parse cached data
     * 
     * @param mixed $record_id
     * @return void
     */
    public function adjudicateCachedRecord($record_id)
    {
        $errors = [];

        try {
            $records_data = $this->getDdpRecordsData($record_id); // get list of records from database 
            $parser = new RecordDataParser($this->project);
            $parser->parse($records_data);
            $record = $parser->getRecord();
            // $all = $parser->getProcessedIdList();
            $excluded_list = $parser->getExcluded();
            $adjudicated_list = $parser->getAdjudicated();

            // $random_errors and $dry_run are used for debug
            $random_errors = false;
            if($random_errors) $this->getRandomError();
            $dry_run = false; // do not persist changes in 'dry run' mode

            // check if the parsed data needs to be saved
            if(!$dry_run) {
                if(!empty($record)) {
                    $save_response = \REDCap::saveData($this->project_id, 'array', $record);
                    if(!empty(@$save_response['errors'])) {
                        $save_errors = implode(';', $save_response['errors']);
                        $message = "Error updating REDCap record {$record_id} - {$save_errors}";
                        throw new \Exception($message, 400);
                    }

                    // log statistics for adjudicated FHIR data
                    $fhirStatsCollector = new FhirStatsCollector($this->project_id, FhirStatsCollector::REDCAP_TOOL_TYPE_CDP_INSTANT);
                    DynamicDataPull::logFhirStatsUsingRecord($fhirStatsCollector, $record);
                }
                // updated the redcap_ddp_records_data table
                $this->markDdpRecordsDataAsExcluded($excluded_list);
                $this->markDdpRecordsDataAsAdjudicated($adjudicated_list);
                // update the redcap_ddp_records table
                $counter = $this->updateDdpRecordsCounter($record_id);
            }
            $excluded = count($excluded_list);
            $adjudicated = count($adjudicated_list);

        } catch (\Exception $e) {
            $message = $e->getMessage();
            $errors[] = $message;
        } finally {
            $response = compact('excluded', 'adjudicated', 'record');
            $response['has_errors'] = !empty($errors);
            $response['errors'] = $errors;
            return $response;
        }
    }

     /**
     * Get a list of record ID that have potential data to be adjudicated.
     * Empty values are not counted.
     * @param integer $next determines if we are loading more data
     * @return array list of IDs with metadata
     */
    public function getDdpRecordsMetadata($offset=0)
    {
        /**
         * provide metadata for the frontend
         * if load_more is true, the frontend will
         * ask for more data
         */
        $getRequestMetadata = function($all_records, $current_offset) {
            $total_records = count($all_records);
            $current_total = $current_offset+self::RECORDS_METADATA_CHUNK_SIZE;
            $load_more = false;
            $next_offset = 0;
            // check if all records have been loaded
            if($current_total<$total_records) {
                $load_more = true;
                $next_offset = $current_offset+self::RECORDS_METADATA_CHUNK_SIZE;
            }
            $metadata = [
                'total_records' => $total_records,
                'next_offset' => $next_offset,
                'load_more' => $load_more,
            ];
            return $metadata;
        };
        $getChunk = function($records, $offset) {
            $length = self::RECORDS_METADATA_CHUNK_SIZE;
            return array_splice($records, $offset, $length);
        };
        $offset = intval($offset);
        $all_potential_adjudications = [];
        $processable_records = $this->getProcessableRecords(); // get list of records from database
        $records_chunk = $getChunk($processable_records, $offset);
        foreach ($records_chunk as $key => $record_id) {
            $records_data =  $this->getDdpRecordsData($record_id);
            $parser = new RecordDataParser($this->project);
            $parser->parse($records_data);
            $potential_adjudications = $parser->getAdjudicated();
            if(count($potential_adjudications)<1) continue;
            $all_potential_adjudications[] = [
                'record' => $record_id,
                'total' => count($potential_adjudications),
            ];
        }
        $metadata = $getRequestMetadata($processable_records, $offset);
        $response = [
            'metadata' => $metadata,
            'record_data' => $all_potential_adjudications,
        ];
        return $response;
    }

    public function ddpRecordsMetadataGenerator()
    {

    }

    /**
     * get a list of records with
     * non-empty and non-adjudicated values
     *
     * @return array
     */
    private function getProcessableRecords()
    {
        $encrypted_empty_value = checkNull(self::getEncryptedEmptyValue());
        /* $getLimitQuery = function($start, $offset) {
            $query_string = '';
            $start = intval($start);
            $offset = intval($offset);
            if($offset<1 || $start<0) return $query_string;
            $query_string = sprintf("LIMIT %u, %u", $start, $offset);
            return $query_string;
        };
        $limit_query = $getLimitQuery($start, $offset); */
        $query_string = sprintf(
            "SELECT records.record
            FROM %s AS cache
            LEFT JOIN %s AS records ON cache.mr_id=records.mr_id
            WHERE records.project_id=%u
            AND cache.adjudicated!=1
            AND NOT(`source_value`<=>%s OR `source_value2`<=>%s)
            GROUP BY records.record
            ORDER BY records.record",
            self::CACHED_RECORDS_DATA_TABLE,
            self::CACHED_RECORDS_TABLE,
            $this->project_id,
            $encrypted_empty_value, $encrypted_empty_value
        );
        $result = db_query($query_string);
        if(!$result) throw new \Exception(sprintf("There was an error retrieving DDP records data from the database for project %u", $this->project_id), 400);
        
        $data = [];
        while($row = db_fetch_assoc($result)) {
            $data[] = @$row['record'];
        }
        return $data;
    }

    /**
     * update a property in the redcap_ddp_records_data table
     * for a list of provided IDs
     *
     * @param int[] $id_list
     * @param string $property
     * @param mixed $value
     * @param string $message
     * @return mixed
     */
    private function massUpdateDdpRecordsData($id_list, $property, $value, $message="updated property")
    {
        if(empty($id_list)) return 0; // exit if no IDs provided
        $addQuotes = function($array) {
            $with_quotes = array_map(function($id){
                return "'{$id}'";
            }, $array);
            return implode(', ', $with_quotes);
        };
        $query_string = sprintf(
            "UPDATE %s AS `data`
            LEFT JOIN %s AS `records` ON `data`.mr_id = `records`.mr_id
            SET `%s`=%s
            WHERE `records`.`project_id`=%u AND `data`.md_id IN (%s)",
            self::CACHED_RECORDS_DATA_TABLE,
            self::CACHED_RECORDS_TABLE,
            $property,
            checkNull($value),
            $this->project_id,
            $addQuotes($id_list)
        );
        $result = db_query($query_string);
        if($result==false) throw new \Exception(sprintf("Error updating the table `%s` in project %u", self::CACHED_RECORDS_DATA_TABLE, $this->project_id), 400);
        $affected_rows = db_affected_rows();
        Logging::logEvent($query_string, self::CACHED_RECORDS_DATA_TABLE, "MANAGE", $this->user_id,"username = '" . db_escape($this->user_id) . "'",$message);
        return $affected_rows;
    }


    /**
     * Count the not adjudicated values for a record.
     * Empty values are not counted.
     *
     * @param mixed $record_id
     * @return int
     */
    private function countNonAdjudicatedValues($record_id)
    {
        $total = 0;
        $encrypted_empty_value = checkNull(self::getEncryptedEmptyValue());
        $query_string = sprintf(
            "SELECT `records`.mr_id, COUNT(*) AS `total`
             FROM %s AS `records`
             LEFT JOIN %s AS `data` ON `records`.mr_id=`data`.md_id
            WHERE project_id=%u AND `records`.record=%s
             AND (`adjudicated`=0 AND `exclude`=0)
             AND NOT(`source_value`<=>%s OR `source_value2`<=>%s)
            GROUP BY project_id, `records`.record",
            self::CACHED_RECORDS_TABLE,
            self::CACHED_RECORDS_DATA_TABLE,
            $this->project_id,
            $record_id,
            $encrypted_empty_value, $encrypted_empty_value
        );
        $result = db_query($query_string);
        if($result==false) throw new \Exception(sprintf("There was an error counting not adjudicated data from the table `%s` in project %u", self::CACHED_RECORDS_DATA_TABLE, $this->project_id), 400);
        if($row=db_fetch_assoc($result)) {
            $total = intval(@$row['total']);
        }
        return $total;
    }

    /**
     * update the count of items to be adjudicated
     * also set the fetch status to QUEUED
     * NOTE: updated_at stores the last fetch date,
     * so should not be changed here
     *
     * @param mixed $record_id
     * @return int
     */
    private function updateDdpRecordsCounter($record_id)
    {
        $total = $this->countNonAdjudicatedValues($record_id);
        $query_string = sprintf(
            "UPDATE %s
            SET item_count=%u
            WHERE project_id=%u AND record=%s",
            self::CACHED_RECORDS_TABLE,
            $total,
            $this->project_id, $record_id
        );
        $result = db_query($query_string);
        if($result==false) throw new \Exception(sprintf("Error updating the table `%s` in project %u", self::CACHED_RECORDS_TABLE, $this->project_id), 400);
        $affected_rows = db_affected_rows();
        Logging::logEvent($query_string, self::CACHED_RECORDS_TABLE, "MANAGE", $record_id,"total = {$total}",$message="updated non-adjudicated item_count to {$total}");
        return $affected_rows;
    }

    /**
     * set all records for the current project in QUEUE fetch_status
     * NOTE: updated_at stores the last fetch date,
     * so should not be changed here
     * 
     * @return void
     */
    public function queueAllRecords()
    {
        // $now = date(self::SOURCE_DATE_FORMAT);
        $query_string = sprintf(
            "UPDATE %s
            SET fetch_status='QUEUED'
            WHERE project_id=%u",
            self::CACHED_RECORDS_TABLE,
            $this->project_id
        );
        $result = db_query($query_string);
        if($result==false) throw new \Exception(sprintf("Error updating the table `%s` in project %u", self::CACHED_RECORDS_TABLE, $this->project_id), 400);
        $affected_rows = db_affected_rows();
        Logging::logEvent($query_string, self::CACHED_RECORDS_TABLE, "MANAGE", $this->user_id,"username = '" . db_escape($this->user_id) . "'",$message="all records have been queued");
        return $affected_rows;
    }

    /**
     * mark a list of ddp_records_data as excluded
     *
     * @param int[] $id_list
     * @return mixed
     */
    public function markDdpRecordsDataAsExcluded($id_list)
    {
        return $this->massUpdateDdpRecordsData($id_list, 'exclude', 1, "records marked as 'exclude'");
    }

    /**
     * mark a list of ddp_records_data as adjudicated
     *
     * @param int[] $id_list
     * @return mixed
     */
    public function markDdpRecordsDataAsAdjudicated($id_list)
    {
        return $this->massUpdateDdpRecordsData($id_list, 'adjudicated', 1, "records marked as 'adjudicated'");
    }

    /**
     * get cached data for a specific record
     * together with associated mapping.
     *
     * @param mixed $record_id
     * @return array 
     */
    public function getDdpRecordsData($record_id)
    {
        $query_string = sprintf(
            "SELECT cache.*, records.record, mapping.*
            FROM %s AS cache
            LEFT JOIN %s AS mapping ON cache.map_id=mapping.map_id
            LEFT JOIN %s AS records ON cache.mr_id=records.mr_id
            WHERE records.project_id=%u
            AND records.record=%s
            AND cache.adjudicated!=1
            ORDER BY records.record, mapping.field_name",
            self::CACHED_RECORDS_DATA_TABLE,
            self::DDP_MAPPING_TABLE,
            self::CACHED_RECORDS_TABLE,
            $this->project_id,
            checkNull($record_id)
        );
        $result = db_query($query_string);
        $cache = [];
        while($row=db_fetch_assoc($result)) {
            $cache[] = $row;
        }
        return $cache;
    }

    /**
     * Calculate the encrypted value of an empty string.
     * This value is generally used when counting data to
     * be adjudicated because empty values must be skipped.
     *
     * @return string
     */
    private static function getEncryptedEmptyValue()
    {
        static $encrypted_empty;
        if(!isset($encrypted_empty)) {
            $empty_string = '';
            $encrypted_empty = encrypt($empty_string, DynamicDataPull::DDP_ENCRYPTION_KEY);
        }
        return $encrypted_empty;
    }

    /**
	 * check if auto-adjudication is allowed at system level
	 */
	public static function isAllowed()
	{
		$config = \System::getConfigVals();
		if(!isset($config['fhir_cdp_allow_auto_adjudication'])) return false;
		$allowed = $config['fhir_cdp_allow_auto_adjudication'] == 1;
		return $allowed;
    }
    
    /**
	 * check if the auto adjudication is enabled for a CDP project
	 */
	public static function isEnabled($project_id) {
        $project = new \Project($project_id);
		$auto_adjudication_enabled = $project->project['fhir_cdp_auto_adjudication_enabled'] ?: 0;
		return boolval($auto_adjudication_enabled);
    }
    
    public static function isEnabledAndAllowed($project_id)
    {
        return self::isAllowed() && self::isEnabled($project_id);
    }
}
    