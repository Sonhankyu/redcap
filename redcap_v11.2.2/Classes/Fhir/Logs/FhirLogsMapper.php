<?php
namespace Vanderbilt\REDCap\Classes\Fhir\Logs;

use SplObserver;
use Vanderbilt\REDCap\Classes\Fhir\FhirClient;

class FhirLogsMapper implements SplObserver
{

    /**
     * date format as used in the database
     */
    const DATE_FORMAT = "Y-m-d\TH:i:s\Z";

    /**
     * table where the logs are stored
     *
     * @var string
     */
    const TABLE_NAME = 'redcap_ehr_fhir_logs';


    /**
     * status for data fetched without HTTP errors
     */
    const STATUS_OK = 200;


    /**
     * ID of the user using the FHIR endpoint
     *
     * @var integer
     */
    private $user_id;

    /**
     * create a FHIR log
     *
     * @param integer $user_id
     */
    public function __construct($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * react to notifications (from the FHIR client)
     *
     * @param SplSubject $subject
     * @param string $event
     * @param mixed $data
     * @return void
     */
    public function update($subject, $event = null, $data = null)
    {
        switch ($event) {
            case FhirClient::NOTIFICATION_ENTRIES_RECEIVED:
                if(!($subject instanceof FhirClient)) break;
                $status = @$data['status'] ?: self::STATUS_OK;
                $this->log([
                    'fhir_id' => @$data['patient_id'],
                    'mrn' => @$data['mrn'],
                    'project_id' => @$data['project_id'],
                    'resource_type' => @$data['category'],
                    'status' => $status,
                    'created_at' => @$data['timestamp'],
                ]);
                break;
            default:
                # code...
                break;
        }
    }

    /**
     * get an instance of the log
     *
     * @param integer $id ID of the log on the database
     * @return FhirLogsMapper
     */
    public static function get($id)
    {
        $query_string = sprintf(
            "SELECT * FROM %s
            WHERE id=%u", self::TABLE_NAME, $id
        );
        $result = db_query($query_string);
        $instance = null;
        if($result && $row=db_fetch_assoc($result)) $instance = new FhirLogEntry($row);
        return $instance;
    }

    /**
     * get all logs occourred after a specified date
     * useful to find out when data has been pulled for a user
     *
     * @param \DateTime $date_time
     * @param integer $project_id
     * @param integer $user_id
     * @param string $mrn
     * @return FhirLogsMapper[]
     */
    public static function getLogsAfterDate($date_time, $project_id, $user_id=null, $mrn=null)
    {
        // convert datetime to string
        if(is_a($date_time, \DateTime::class)) $date_time = $date_time->format(self::DATE_FORMAT);
        $query_string = sprintf(
            "SELECT * FROM %s
            WHERE created_at>='%s'
            AND project_id=%u",
            self::TABLE_NAME,
            db_real_escape_string($date_time),
            db_real_escape_string($project_id)
        );
        // select only specified MRN if provided
        if($user_id) $query_string .= sprintf(" AND user_id=%u", db_real_escape_string($user_id));
        if($mrn) $query_string .= sprintf(" AND mrn='%s'", db_real_escape_string($mrn));
        $query_string .= " ORDER BY created_at DESC";
        $result = db_query($query_string);
        $list = [];
        while ($row=db_fetch_assoc($result)) {
            $list[] = new FhirLogEntry($row);
        }
        return $list;
    }

    /**
     * get logs based on some criteria
     * multiple criterias can be specified
     *
     * @param array $criteria ["`key`='value'", "`key`<'value'", ...]
     * @return array
     */
    public static function getLogs($criteria, $start=0, $offset=100)
    {
        $wheres = implode(" AND ", $criteria);
        if(empty($wheres)) $wheres = 1;
        $query_string = sprintf(
            "SELECT * FROM `%s` WHERE %s
            ORDER BY `created_at` DESC
            LIMIT %u, %u",
            self::TABLE_NAME, $wheres,
            $start, $offset
        );
        $result = db_query($query_string);
        $results = [];
        while($row=db_fetch_assoc($result)) $results[] = $row;
        return $results;
    }

    /**
     * store a log on the database
     *
     * @param array $params
     * @return bool
     */
    public function log($params)
    {
        // helper function to enclose string in quotes
        $add_quotes = function($string) {
            return implode(array("'", $string, "'"));
        };
        $query_fields = array(
                'user_id' => checknull($this->user_id),
                'fhir_id' => $add_quotes(@$params['fhir_id']),
                'mrn' => $add_quotes(@$params['mrn']),
                'project_id' => @$params['project_id'],
                'resource_type' => $add_quotes(@$params['resource_type']),
                'status' => $add_quotes(@$params['status']),
                'created_at' => $add_quotes(@$params['created_at']),
        );
        $keys = array_keys($query_fields);
        $values = array_values($query_fields);
        $query_string = sprintf("INSERT INTO %s", db_real_escape_string(self::TABLE_NAME));
        $query_string .= sprintf(" (%s) VALUES (%s)", implode(', ', $keys), implode(', ', $values));
        $result = db_query($query_string);
        if(!$result) throw new \Exception("Error saving FHIR logs on the database", 1);
        return $result;
    }


}