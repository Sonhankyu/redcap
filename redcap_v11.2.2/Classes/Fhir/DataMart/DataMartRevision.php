<?php


namespace Vanderbilt\REDCap\Classes\Fhir\DataMart
{

    use Logging;
    use REDCap;
    use Vanderbilt\REDCap\Classes\Fhir\Logs\FhirLogsMapper;
    use Vanderbilt\REDCap\Classes\Fhir\FhirUser;
    
    /**
     * Model of the DataMart revision that is saved on the database
     * 
     * exposed properties:
     * @property integer $id The primary key for the model
     * @property integer $user_id ID of the user creating the revision
     * @property integer $project_id ID of the project associated to this revision
     * @property integer $request_id ID of the request associated to this revision
     * @property string $request_status status of the request (if applicable)
     * @property array $mrns list of MRN numbers 
     * @property \DateTime|string $date_min minimum date for temporal data
     * @property \DateTime|string $date_max maximum date for temporal data
     * @property array $fields list of fields to use when fetching data
     * @property boolean $approved the revision has been approved by an administrator
     * @property \DateTime $created_at creation date
     * @property \DateTime $executed_at date of first execution
     */
    class DataMartRevision implements \JsonSerializable
    {

        /**
         * datetime in FHIR compatible format
         * https://www.hl7.org/fhir/datatypes.html#dateTime
         */
        const FHIR_DATETIME_FORMAT = "Y-m-d\TH:i:s\Z";

        /**
         * The primary key for the model
         *
         * @var int
         */
        private $id;

        /**
         * ID of the project associated to this revision
         *
         * @var integer
         */
        private $project_id;

        /**
         * ID of the user creating the revision
         *
         * @var int
         */
        private $user_id;
        
        /**
         * ID of the request associated to this revision
         *
         * @var integer
         */
        private $request_id;

        /**
         * status of the request (if applicable)
         *
         * @var string
         */
        private $request_status;

        /**
         * list of MRN numbers 
         *
         * @var array
         */
        private $mrns = array();

        /**
         * minimum date for temporal data
         *
         * @var \DateTime|string
         */
        private $date_min;

        /**
         * maximum date for temporal data
         *
         * @var \DateTime|string
         */
        private $date_max;

        /**
         * list of fields to use when fetching data
         *
         * @var array
         */
        private $fields = array();

        /**
         * the revision has been approved by an administrator
         *
         * @var boolean
         */
        private $approved = false;

        /**
         * creation date
         *
         * @var \DateTime
         */
        private $created_at;

        /**
         * date of first execution
         *
         * @var \DateTime
         */
        private $executed_at;

        /**
         * list of the instance variables that are public  for reading
         *
         * @var array
         */
        private static $readable_variables = array(
            'id',
            'project_id',
            'request_id',
            'user_id',
            'mrns',
            'date_min',
            'date_max',
            'fields',
            'approved',
            'created_at',
            'executed_at',
            'request_status',
        );

        /**
         * list of keys that can be provided in constructor
         *
         * @var array
         */
        private static $constructor_keys = array(
            'id',
            'project_id',
            'request_id',
            'user_id',
            'fields',
            'date_min',
            'date_max',
            'mrns',
            'approved',
            'created_at',
            'executed_at',
        );

        private static $table_name = 'redcap_ehr_datamart_revisions';
        private static $request_table_name = 'redcap_todo_list';

        /**
         * fields in the revisions table
         * used to build the update query for the database
         *
         * @var array
         */
        private static $fillable = array(
            'project_id',
            'request_id',
            // 'user_id',
            'mrns',
            'date_min',
            'date_max',
            'fields',
            'approved',
            'created_at',
            'executed_at',
        );

        private static $string_delimiter = "\n";
        private static $dateTimeFormat = 'Y-m-d H:i:s';
        private static $mandatory_fields = array(
            'project_id|request_id', //project_id OR request_id must be present
            'user_id',
            // 'mrns',
            'fields',
        );

        /**
         * constructor
         *
         * @param array $params an array with any value listed in self::$constructor_keys
         */
        function __construct($params=array())
        {
            try {
                $this->checkRequirements($params);
                // cycle through the permitetd constructor keys
                foreach (self::$constructor_keys as $key) {
                    if(array_key_exists($key, $params)) $this->set($key, $params[$key]);
                }
            } catch (\Exception $e) {
                $messages = array(
                    'Error instantianting the revision.',
                    $e->getMessage(),
                );
                throw new \Exception(implode("\n", $messages));
            }
        }

        /**
         *
         * @return FhirUser
         */
        public function getCurrentUser()
        {
            global $userid;
            $user_id = \User::getUIIDByUsername($userid); // get current user
            $project_id = $this->project_id;
            $fhir_user = new FhirUser($user_id, $project_id);
            return $fhir_user;
        }

        /**
         * check minimum requirements from revision creation
         *
         * @param array $params
         * @return void
         */
        private function checkRequirements($params)
        {
            foreach (self::$mandatory_fields as $field) {
                $valid = false;
                foreach (array_keys($params) as $key) {
                    preg_match("/^{$field}$/", $key, $matches);
                    $valid = !empty($matches);
                    if($valid) break;
                }
                if(!$valid)
                    throw new \Exception("Mandatory field '{$field}' is missing.", 1);
            }
        }

        /**
         * get Data Mart settings for a project
         * the settings are divided in revisions
         *
         * @param int $project_id
         * 
         * @return array list of DataMartRevision
         */
        public static function all($project_id)
        {
            $select_query = self::getSelectQuery();
            $order_by_query_clause = self::getOrderByQueryClause();
            $query = sprintf($select_query." AND r.project_id = %d ".$order_by_query_clause, db_real_escape_string(intval($project_id)));
            $result = db_query($query);
            //print_array($query);
            
            if(!$result) return;

            $revisions = array();
            while($data = db_fetch_array($result))
            {
                $revision = new self($data);
                $revisions[] = $revision;
            }

            if( empty($revisions) ) return array();
            return $revisions;
        }

        /**
         * return the active revision for a project
         * the revision must be approved and not soft-deleted
         *
         * @param integer $project_id
         * @return DataMartRevision|false
         */
        public static function getActive($project_id)
        {
            $query_string = sprintf("SELECT id
                            FROM  %s
                            WHERE project_id=%u
                            AND is_deleted!=1
                            ORDER BY created_at DESC, id DESC
                            LIMIT 1",
                            self::$table_name, $project_id);
            $result = db_query($query_string);
            if($result && $row = db_fetch_assoc($result))
            {
                $revision_id = $row['id'];
                return self::get($revision_id);
            }
            return false;
        }

        /**
         * check if a revision is active
         *
         * @throws \Exception
         * @return boolean
         */
        public function isActive()
        {
            $project_id = $this->project_id;
            $active_revision = self::getActive($project_id);
            if(!$active_revision) throw new \Exception("There are no active revisions for this project", 400);
            // check if the revision we are trying to run is the active one
            if($this->id !== $active_revision->id) throw new \Exception("This is not the active revision for this project", 400);
            return true;
        }

        /**
         * get a revision from the database using the ID
         *
         * @param int $id
         * @return DataMartRevision|false
         */
        public static function get($id)
        {
            $select_query = self::getSelectQuery();
            $order_by_query_clause = self::getOrderByQueryClause();
            $query_string = sprintf($select_query." AND r.id=%u ".$order_by_query_clause, db_real_escape_string(intval($id)));

            $result = db_query($query_string);
            if($result && $params=db_fetch_assoc($result)) return new self($params);
            else return false;
        }

        /**
         * create a revision
         *
         * @param array $settings
         * 
         * @return DataMartRevision
         */
        public static function create($settings)
        {
            $revision = new self($settings);
            return $revision->save();
        }

        /**
         * persist a revision to the database
         * 
         * @throws Exception if the revision can not be saved
         *
         * @return DataMartRevision
         */
        public function save()
        {
            $new_instance = empty($this->id); //check if we are creating a new instance
            if($new_instance) {
                $this->set('created_at', NOW); // set the creation date using PHP time
                $query_string = $this->getInsertQuery();
            }else {
                $query_string = $this->getUpdateQuery();
            }
            if($result = db_query($query_string)) {
                if($id=db_insert_id()) $this->id = $id; // set the revision ID if inserting
                $log_message = ($new_instance===true) ? 'Create Clinical Data Mart revision' : 'Update Clinical Data Mart revision';
                \Logging::logEvent($query_string, "redcap_ehr_datamart_revisions", "MANAGE", $this->project_id, sprintf("revision_id = %u", $this->id), $log_message);
                return self::get($this->id); // get the revision from the database
            }else {
                throw new \Exception("Could not save the revision to the database",1);
            }
        }

        /**
         * set the exectuted_at property of a revision
         *
         * @param string $time
         * @return DataMartRevision
         */
        public function setExecutionTime($time=null)
        {
            $time = $time ? $time : NOW;
            return $this->set('executed_at', $time);
        }

        /**
         * set the request_id property of a revision
         *
         * @param string $request_id
         * @return DataMartRevision
         */
        public function setRequestId($request_id)
        {
            return $this->set('request_id', $request_id);
        }

        /**
         * set the project_id property of a revision
         *
         * @param string $project_id
         * @return DataMartRevision
         */
        public function setProjectId($project_id)
        {
            return $this->set('project_id', $project_id);
        }

        /**
         * approve a revision
         * 
         * @return DataMartRevision
         */
        public function approve()
        {
            $revision = $this->set('approved', true);
            // create empty fields for each mrn in this revision which is not already available in the project
            $revision->createRecords();
            return $revision;
        }

        /**
         * delete the revision
         * defaults to a soft delete
         *
         * @param boolean $soft_delete
         * @throws Exception if no revision can be returned
         * 
         * @return DataMartRevision
         */
        public function delete($soft_delete=true)
        {
            if($soft_delete==true)
            {
                $query_string = sprintf("UPDATE %s SET is_deleted=1 WHERE id=%u", 
                    self::$table_name,
                    db_real_escape_string($this->id)
                );
            }else
            {
                $query_string = sprintf("DELETE FROM %s WHERE id=%u", 
                    self::$table_name,
                    db_real_escape_string($this->id)
                );
            }
            
            // check if query is successful and the $id is valid
            if($result = db_query($query_string))
            {
                \Logging::logEvent($query_string,"redcap_ehr_datamart_revisions","MANAGE",$this->project_id,sprintf("revision_id = %u",$this->id),'Delete Clinical Data Mart revision');

                return true;
            }else
            {
                throw new \Exception("Could't delete the revision from the database",1);
            }
        }

        /**
         * get a query string to UPDATE a Revision on the database 
         *
         * @return string query
         */
        private function getUpdateQuery()
        {
            $db_formatted = $this->toDatabaseFormat();
            $query_string = sprintf("UPDATE %s", db_real_escape_string(self::$table_name));
            $set_fields = array();
            foreach (self::$fillable as $key)
            {
                if(!empty($db_formatted->{$key}))
                    $set_fields[] = sprintf( "%s=%s", $key, $db_formatted->{$key} );
            }
            $query_string .= " SET ".implode(', ', $set_fields);
            $query_string .= sprintf(" WHERE id=%d", db_real_escape_string($this->id));
            return $query_string;
        }

        /**
         * get a query string to INSERT into the database a new Revision
         *
         * @return string query
         */
        private function getInsertQuery()
        {
            $db_formatted = $this->toDatabaseFormat();
            $query_fields = array(
                'user_id' => $db_formatted->user_id,
                'mrns' => $db_formatted->mrns,
                'date_min' => $db_formatted->date_min,
                'date_max' => $db_formatted->date_max,
                'fields' => $db_formatted->fields,
                'approved' => $db_formatted->approved,
                'created_at' => $db_formatted->created_at,
            );
            if($project_id = $db_formatted->project_id) $query_fields['project_id'] = $project_id;
            if($request_id = $db_formatted->request_id) $query_fields['request_id'] = $request_id;
            $keys = array_keys($query_fields);
            $values = array_values($query_fields);

            $query_string = sprintf("INSERT INTO %s", db_real_escape_string(self::$table_name));
            $query_string .= sprintf(" (%s) VALUES (%s)", implode(', ', $keys), implode(', ', $values));
            return $query_string;
        }

        /**
         * get the SELECT query for the revisions
         * select only the revisions that have not been marked as deleted
         *
         * @return string
         */
        private static function getSelectQuery()
        {
            return sprintf("SELECT r.*, t.status AS request_status FROM redcap_ehr_datamart_revisions AS r
                            LEFT JOIN redcap_todo_list AS t ON r.request_id=t.request_id
                            WHERE is_deleted != 1",
                            self::$table_name, self::$request_table_name);
        }

        /**
         * get the ORDER BY clause query for the revisions
         *
         * @return string
         */
        private static function getOrderByQueryClause()
        {
            return "ORDER BY created_at ASC";
        }

        /**
         * get a revision from the database using the request_id
         *
         * @param int $request_id
         * @return DataMartRevision|false
         */
        public static function getRevisionFromRequest($request_id)
        {
            $select_query = self::getSelectQuery();
            $order_by_query_clause = self::getOrderByQueryClause();
            $query_string = sprintf($select_query." AND r.request_id=%u ".$order_by_query_clause, db_real_escape_string(intval($request_id)));

            $result = db_query($query_string);
            if($result && $params=db_fetch_assoc($result)) return new self($params);
            else return false;
        }

        /**
         * get a range of dates compatible with the FHIR specifiction
         *
         * @return array
         */
        public function getFHIRDateRange()
        {
            $date_min = $this->date_min;
            $date_max = $this->date_max;
            // check if $date_max is in the future
            if( !empty($date_max) && $date_max->getTimestamp() >= time() ) $date_max = '';
            if( !empty($date_min) && !empty($date_max) && $date_min > $date_max)
            {
                // If min is bigger than max, then simply swap them
                $temp_max = $date_max;
                $date_max = $date_min;
                $date_min = $temp_max;
            }
            // Reformat dates for temporal window
            if( !empty($date_min) ) $date_min = $date_min->setTime(0, 0, 0); //->format(self::FHIR_DATETIME_FORMAT);
            if( !empty($date_max) ) $date_max = $date_max->setTime(23, 59, 59); //->format(self::FHIR_DATETIME_FORMAT);

            return array(
                'date_min' => $date_min,
                'date_max' => $date_max,
            );
        }

        /**
         * get a normalized array of mapped fields
         * along with date range suitable for
         * fetching and saving FHIR data
         *
         * @param string $mrn
         * @return array
         */
        public function getNormalizedMapping($mrn)
        {
            $dateRange = $this->getTemporalDataDateRangeForMrn($mrn);
            list($date_min, $date_max) = $dateRange;
            $fields = $this->fields;
            $normalized = [];
            foreach ($fields as $field) {
                $normalized[] = array('field'=>$field, 'timestamp_min'=>$date_min, 'timestamp_max'=>$date_max);
            }
            return $normalized;
        }

        /**
         * return an object in a db compatible format
         *
         * @return object
         */
        public function toDatabaseFormat()
        {
            $date_min = ($this->date_min instanceof \DateTime) ? $this->date_min->format(self::$dateTimeFormat) : null;
            $date_max = ($this->date_max instanceof \DateTime) ? $this->date_max->format(self::$dateTimeFormat) : null;
            $executed_at = ($this->executed_at instanceof \DateTime) ? $this->executed_at->format(self::$dateTimeFormat) : null;
            $created_at = ($this->created_at instanceof \DateTime) ? $this->created_at->format(self::$dateTimeFormat) : null;
            
            $db_format = (object) array(
                'id' => db_real_escape_string($this->id),
                'project_id' => db_real_escape_string($this->project_id),
                'request_id' => db_real_escape_string($this->request_id),
                'user_id' => db_real_escape_string($this->user_id),
                'mrns' => checkNull( implode(self::$string_delimiter, $this->mrns)),
                'date_min' => checkNull($date_min),
                'date_max' => checkNull($date_max),
                'fields' => checkNull( implode(self::$string_delimiter, $this->fields)),
                'approved' => db_real_escape_string((int)!!$this->approved),
                'executed_at' => checkNull($executed_at),
                'created_at' => checkNull($created_at),
            );
            // remove null or empty values
            /* foreach ($db_format as $key => $value) {
                if(empty($value)) unset($db_format->{$key});
            } */
            return $db_format;
        }

        /**
         * check if a revision is duplicated
         *
         * @param array $settings
         * @return boolean
         */
        public function isDuplicate($settings)
        {
            $date_min = self::getDate($settings['date_min']);
            $date_max = self::getDate($settings['date_max']);
            $sameSettings = self::compareArrays($this->mrns, $settings['mrns']) &&
                            self::compareArrays($this->fields, $settings['fields']) &&
                            self::compareDates($this->date_min, $date_min) &&
                            self::compareDates($this->date_max, $date_max);
            return $sameSettings;
        }

        /**
         * show if the revision has already been executed
         *
         * @return boolean
         */
        public function hasBeenExecuted()
        {
            return !empty($this->executed_at);
        }

        /**
         * get the SQL query for fetchable MRNs
         *
         * @return string
         */
        public function getQueryForFetchableData()
        {
            $now = new \DateTime();
            $formatted_now = $now->format('Y-m-d H:i');
            $us = chr(31); //unit separator
            $query_string = sprintf(
                "SELECT
                GROUP_CONCAT(CASE WHEN `field_name` = 'mrn' THEN value ELSE NULL END ORDER BY `value` ASC SEPARATOR '{$us}') AS `mrn`,
                GROUP_CONCAT(CASE WHEN `field_name` = 'fetch_date_start' THEN value ELSE NULL END ORDER BY `value` ASC SEPARATOR '{$us}') AS `fetch_date_start`,
                GROUP_CONCAT(CASE WHEN `field_name` = 'fetch_date_end' THEN value ELSE NULL END ORDER BY `value` ASC SEPARATOR '{$us}') AS `fetch_date_end`
                FROM `redcap_data` WHERE `project_id`=%u
                AND `field_name` IN ('mrn','fetch_date_start','fetch_date_end')
                GROUP BY `record`
                HAVING (
                    (fetch_date_start IS NULL OR fetch_date_start<%s) AND (fetch_date_end IS NULL OR fetch_date_end>%s)
                )",
                $this->project_id,
                checkNull($formatted_now), checkNull($formatted_now)
            );
            return $query_string;
        }

        /**
         * get the total amount of MRNs that have not
         * been fetched successfully after the creation date
         * of the revision. This is usually used for users
         * with limited privileges.
         * 
         * NOTE: this is user indipendent, i.e. any user could have fetched the data
         *
         * @return int
         */
        public function getTotalNonFetchedMrns()
        {
            $created_at = $this->created_at->format(self::$dateTimeFormat);
            $sub_query = $this->getQueryForFetchableData();
            $query_string = sprintf(
                "SELECT count(`mrn`) AS `total` FROM ($sub_query) AS `rotated_data`
                    WHERE `mrn` NOT IN (
                        SELECT `mrn` FROM `%s`
                        WHERE `project_id` = %u
                        AND `status` = %s AND `created_at`>%s
                    )",
                FhirLogsMapper::TABLE_NAME,
                $this->project_id,
                checkNull(FhirLogsMapper::STATUS_OK), checkNull($created_at)
            );
            $result = db_query($query_string);
            if($row=db_fetch_assoc($result)) {
                return $total = intval(@$row['total']);
            }
            return 0;
        }

        /**
         * get the total number of
         * fetchable MRNs for the current user
         *
         * @return int
         */
        public function getTotalFetchableMrnsProxy()
        {
            $fhir_user = $this->getCurrentUser();
            if($fhir_user->can_repeat_revision) {
                return $this->getTotalFetchableMrns();
            }
            return $this->getTotalNonFetchedMrns();
        }


        /**
         * count MRNs with a fetchable date range
         *
         * @return int
         */
        public function getTotalFetchableMrns()
        {
            $sub_query = $this->getQueryForFetchableData();
            $query_string = "SELECT COUNT(`mrn`) as `total` FROM ($sub_query) AS `rotated_data`";
            $result = db_query($query_string);
            if($row=db_fetch_assoc($result)) {
                return $total = intval(@$row['total']);
            }
            return 0;
        }

        /**
         * count MRNs in a project
         *
         * @return int
         */
        public function getTotalMrns()
        {
            $query_string = sprintf(
                "SELECT COUNT(DISTINCT `value`) as `total` FROM `redcap_data`
                WHERE `project_id`=%u AND `field_name`='mrn'",
                $this->project_id
            );
            $result = db_query($query_string);
            if($row=db_fetch_assoc($result)) {
                return $total = intval(@$row['total']);
            }
            return 0;
        }

        /**
         * check if a MRN has already been fetched
         * by a user using the current revision
         * 
         * @param FhirUser $user
         * @param string $mrn
         * @return bool
         */
        public function hasPreviouslyFetchedMrn($fhir_user, $mrn)
        {
            $created_at = $this->created_at->format(self::$dateTimeFormat);
            $query_string = sprintf(
                "SELECT 1 FROM `%s`
                WHERE project_id = %u AND user_id = %u
                AND status != %s AND created_at>%s
                AND mrn = %s",
                FhirLogsMapper::TABLE_NAME,
                $this->project_id, $fhir_user->id,
                checkNull(FhirLogsMapper::STATUS_OK),
                checkNull($created_at), $mrn
            );
            $result = db_query($query_string);
            $count = intval(db_num_rows($result));
            return boolval($count>0);
        }

        /**
         * check if a MRN can be fetched using this revision.
         * Users that can repeat revisions can always fetch data.
         * Users with minor privileges cannort fetch data twice
         * for an MRN in the same revision
         *
         * @param FhirUser $user
         * @param string $mrn
         * @return boolean
         */
        public function canFetchMrn($fhir_user, $mrn)
        {
            if($fhir_user->can_repeat_revision) return true;
            return !$this->hasPreviouslyFetchedMrn($fhir_user, $mrn);
        }

        /**
         * return a list of the MRNs stores in the records of the revision's project
         *
         * @return array
         */
        public function getProjectMrnList()
        {
            $query_string = sprintf(
                "SELECT DISTINCT value FROM redcap_data
                WHERE project_id=%u
                AND field_name='mrn'",
                $this->project_id
            );
            $result = db_query($query_string);
            $mrns = array();
            while($row = db_fetch_object($result))
            {
                $mrns[] = $row->value;
            }
            return $mrns;
        }

        /**
         * Get the next MRN with a valid individual
         * date range (as set in the "project settings" instrument).
         * If no MRN is provided then the first one will be returned
         *
         * @param FhirUser $fhirUser
         * @param string $mrn
         * @return string|null
         */
        public function getNextMrnWithValidDateRange($fhirUser, $mrn=null)
        {
            $getMrnSubsetQuery = function() use ($fhirUser) {
                if($fhirUser->can_repeat_revision) return '';
                $created_at = $this->created_at->format(self::$dateTimeFormat);
                $subset_query = sprintf(" AND `mrn` NOT IN (
                    SELECT `mrn` FROM `%s` WHERE `project_id`=%u
                    AND `status`=%s AND `created_at`>%s
                )", FhirLogsMapper::TABLE_NAME, $this->project_id,
                checkNull(FhirLogsMapper::STATUS_OK), checkNull($created_at));
                return $subset_query;
            };
            $now = new \DateTime();
            $formatted_now = $now->format('Y-m-d H:i');
            $us = chr(31); // unit_separator
            $mrn = $mrn ?: ''; //default to blank string
            $query_string = sprintf(
                "SELECT `record`,
                GROUP_CONCAT(CASE WHEN `field_name` = 'mrn' THEN value ELSE NULL END ORDER BY `value` ASC SEPARATOR '{$us}') AS `mrn`,
                GROUP_CONCAT(CASE WHEN `field_name` = 'fetch_date_start' THEN value ELSE NULL END ORDER BY `value` ASC SEPARATOR '{$us}') AS `fetch_date_start`,
                GROUP_CONCAT(CASE WHEN `field_name` = 'fetch_date_end' THEN value ELSE NULL END ORDER BY `value` ASC SEPARATOR '{$us}') AS `fetch_date_end`
                FROM `redcap_data`
                WHERE `project_id`=%u
                AND `field_name` IN ('mrn','fetch_date_start','fetch_date_end')
                GROUP BY `record`
                HAVING (
                    `mrn`>IFNULL(%s, '')
                    %s
                    AND (fetch_date_start IS NULL OR fetch_date_start<%s) AND (fetch_date_end IS NULL OR fetch_date_end>%s)
                )
                ORDER BY `mrn` ASC",
                $this->project_id, checkNull($mrn),
                $getMrnSubsetQuery(), // insert the subquery to limit MRNs based on user privileges
                checkNull($formatted_now), checkNull($formatted_now)
            );
            $result = db_query($query_string);
            if($row=db_fetch_assoc($result)) return @$row['mrn'];
            return null;
        }

        /**
         * check if a project contains an MRN
         *
         * @return boolean
         */
        public function projectContainsMrn($mrn)
        {
            $query_string = sprintf(
                "SELECT * FROM redcap_data
                WHERE project_id=%u
                AND field_name='mrn'
                AND value='%s'",
                $this->project_id,
                db_real_escape_string($mrn)
            );
            $result = db_query($query_string);
            return db_num_rows($result);
        }

        /**
         * create empty records if the revision contains MRNs
         *
         * @return array results from saved data
         */
        public function createRecords()
        {
            // remove MRNs if already existing in project
            $filterRecords = function($mrns=[]) {
                $quotedList = "'" . implode("','", $mrns) . "'";
                $query_string = sprintf(
                    "SELECT `record`, `field_name`, `value`
                    FROM `redcap_data` WHERE project_id=%u
                    AND `field_name`='mrn'
                    AND `value` IN (%s)",
                    $this->project_id, $quotedList
                );
                $result = db_query($query_string);

                while($row=db_fetch_assoc($result)) {
                    $index = array_search(@$row['value'], $mrns);
                    if($index>=0) unset($mrns[$index]);
                }
                return $mrns;
            };
            if(count($this->mrns)===0) return;
            $project = new \Project($this->project_id);
            $event_id = $project->firstEventId;
            $mrns = $filterRecords($this->mrns);
            foreach ($mrns as $mrn) {
                $record_id = \DataEntry::getAutoId($this->project_id); // get auto record number
                $record = [
                    $record_id => [
                        $event_id => [
                            'record_id' => $record_id,
                            'mrn' => $mrn,
                        ]
                    ]
                ];
                $result = REDCap::saveData($this->project_id, 'array', $record);
                $errors = @$result['errors'];
                if(!empty($errors)) {
                    $errorText = implode(PHP_EOL, $errors);
                    Logging::logEvent('', 'redcap_data', "ERROR", $record_id, $errorText, "Error creating new record in the Data Mart project ID {$this->project_id}");
                }
            }
        }

        /**
         * compare 2 arrays
         *
         * @param array $array_a
         * @param array $array_b
         * @return void
         */
        private static function compareArrays($array_a, $array_b)
        {
            sort($array_a);
            sort($array_b);
            return $array_a == $array_b;
        }

        private static function compareDates($date_a, $date_b)
        {
            $date_a = ($date_a instanceof \DateTime) ? $date_a->format(self::$dateTimeFormat) : $date_a;
            $date_b = ($date_b instanceof \DateTime) ? $date_b->format(self::$dateTimeFormat) : $date_b;
            return $date_a === $date_b;
        }

        /**
         * transform a string into a DateTime or in a null value
         *
         * @param string $date_string
         * @return null|\DateTime
         */
        private static function getDate($date_string)
        {
            if(empty($date_string)) return null;
            $time = strtotime($date_string);
            $date_time = new \DateTime();
            $date_time->setTimestamp($time);
            return $date_time;
        }


        /**
         * get info about the user who created the revision
         */
        public function getCreator()
        {
            return new FhirUser($this->user_id, $this->project_id);
        }

        /**
         * get the data of the revision
         *
         * @return array
         */
        public function getData()
        {
            return array(
                'mrns' => $this->mrns,
                'fields' => $this->fields,
                'dateMin' => ($this->date_min instanceof \DateTime) ? $this->date_min->format(self::$dateTimeFormat) : '',
                'dateMax' => ($this->date_max instanceof \DateTime) ? $this->date_max->format(self::$dateTimeFormat) : '',
            );
        }

        /**
         * magic getter for properties specified in self::$readable_variables
         *
         * @param string $name
         * @return void
         */
        public function __get($property)
        {
            if (property_exists($this, $property) && in_array($property, self::$readable_variables)) {
                return $this->$property;
            }

            $trace = debug_backtrace();
            trigger_error(
                'Undefined property via __get(): ' . $property .
                ' in ' . $trace[0]['file'] .
                ' on line ' . $trace[0]['line'],
                E_USER_NOTICE);
            return null;
        }

        /**
         * setter for instance properties
         * helps to set the right format for dates, arrays and booleans
         *
         * @param string|array $property
         * @param mixed $value
         * @return DataMartRevision
         */
        private function set($property, $value=null)
        {
            if(!property_exists($this, $property)) return $this;
            switch ($property) {
                case 'mrns':
                case 'fields':
                    $list = $value;
                    // convert string to array
                    if(!is_array($list))
                    {
                        $text = trim($value);
                        if(strlen($text)===0)
                        {
                            // empry array if string is empty
                            $list = array();
                        }else {
                            $list = explode(self::$string_delimiter, $text);
                        }
                    }
                    $list = array_unique($list, SORT_STRING); // discard duplicates
                    $this->{$property} = $list;
                    break;
                case 'date_min':
                case 'date_max':
                case 'executed_at':
                case 'created_at':
                    if (is_a($value, \DateTime::class))
                    {
                        $this->{$property} = $value; // assign it if it is a DateTime
                    }else
                    {
                        $this->{$property} = self::getDate($value);
                    }
                    break;
                case 'approved':
                    $this->{$property} =  boolval($value); //convert to boolean
                    break;
                default:
                    $this->{$property} = $value;
                    break;
            }
            return $this;
        }

        /**
         * Get the date range for an MRN considering both revision level date range
         * and record level date range; record level date range has higher priority.
         * 
         * This date range affects temporal data like labs and vitals.
         *
         * @param string $mrn
         * @return DateTime[] ['date_min', 'date_max']
         */
        public function getTemporalDataDateRangeForMrn($mrn)
        {
            if(empty($this->project_id)) return;
            // set default values
            $date_min = '';
            $date_max = '';
            $datamart_record = new DataMartRecord($this->project_id, $mrn);
            $record_date_range = $datamart_record->getDateRange();
            // priority to record date range
            if(empty($record_date_range))
            {
                // use the revision date range if no date range has benn specified in the 'Project Settings' instrument 
                $revision_date_range = $this->getFHIRDateRange(); // get a date range compatible with the FHIR specification
                $date_min = $revision_date_range['date_min'];
                $date_max = $revision_date_range['date_max'];
            }else {
                // use the date range specified in the instrument 'Project Settings' if available
                $date_min = $record_date_range['date_min'];
                $date_max = $record_date_range['date_max'];
            }
            return [$date_min, $date_max];
        }

        /**
        * Returns data which can be serialized
        * this format is used in the client javascript app
        *
        * @return array
        */
        public function jsonSerialize()
        {
            $serialized = array(
                'metadata' => array(
                    'id' => $this->id,
                    'request_id' => $this->request_id,
                    'request_status' => $this->request_status,
                    'date' => $this->created_at->format(self::$dateTimeFormat),
                    // 'date' => ($this->created_at instanceof DateTime) ? $this->created_at->format(self::$dateTimeFormat) : '',
                    'executed' => $this->hasBeenExecuted(),
                    'executed_at' => ($this->executed_at instanceof \DateTime) ? $this->executed_at->format(self::$dateTimeFormat) : '',
                    'approved' => boolval($this->approved),
                    'creator' => $this->getCreator(),
                    'total_project_mrns' => $this->getTotalMrns(),
                    'total_non_fetched_mrns' => $this->getTotalNonFetchedMrns(),
                    'total_fetchable_mrns' => $this->getTotalFetchableMrnsProxy(),
                ),
                'data' => $this->getData(),
            );
            return $serialized;
        }

        /**
         * print a DataMart Revision as a string
         *
         * @return string
         */
        public function __toString()
        {
            $string = '';
            $string .= $this->id;
            return $string;
        }
        
    }
}