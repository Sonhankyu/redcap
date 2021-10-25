<?php

namespace Vanderbilt\REDCap\Classes\Fhir\ClinicalDataPull\AutoAdjudication;

use Vanderbilt\REDCap\Classes\Fhir\ClinicalDataPull\AutoAdjudication\Strategies\AdjudicationStrategy;
use Vanderbilt\REDCap\Classes\Fhir\ClinicalDataPull\AutoAdjudication\Strategies\AdjudicationStrategyFactory;

class RecordDataParser
{

    /**
     * keep track of the parsed data and preselected values
     *
     * @var array
     */
    private $data = [];
    /**
     * laceholder for a REDCap record
     *
     * @var array
     */
    private $record = [];
    /**
     * list of record data IDs that will be excluded
     *
     * @var array
     */
    private $to_be_excluded = [];
    /**
     * list of record data IDs that will be adjududicated
     *
     * @var array
     */
    private $to_be_adjudicated = [];

    const FLAG_ADJUDICATED = 'adjudicated';
    const FLAG_EMPTY = 'empty';
    const FLAG_EXISTS = 'exists';
    const FLAG_NOT_BEST_OPTION = 'not_best_option';
    const FLAG_NO_TARGETS = 'no_targets';

    private $processed_list = [];
    
    /**
    * Auto Adjudicate data in CDP projects
    *
    * @param int $project_id
    */
    public function __construct($project)
    {
        $this->project = $project;
    }

    public function getRecord()
    {
        return $this->record;
    }

    /**
     * get a list of processed records_data ID
     * if a flag is specified, then a specific group is returned
     * otherwise
     *
     * @param [type] $flag
     * @return void
     */
    public function getProcessedIdList($flag=null)
    {
        $list = $this->processed_list;
        if(empty($flag)) {
            $all = array_reduce($list, function($carry, $group) {
                $carry = array_merge($carry, $group);
                return $carry;
            }, $initial=[]);
            return $all;
        }
        $group = @$list[$flag] ?: [];
        return $group;
    }

    /**
     * get a list of records_data ID that will not be adjudicated
     * NOTE: empty values are excluded from the list
     *
     * @return array
     */
    public function getExcluded()
    {
        $flags = [
            // self::FLAG_EMPTY, //skip empty values
            self::FLAG_EXISTS,
            self::FLAG_NOT_BEST_OPTION,
            self::FLAG_NO_TARGETS,
        ];
        $list = [];
        foreach ($flags as $flag) {
            $group = $this->getProcessedIdList($flag);
            $list = array_merge($list, $group);
        }
        return $list;
    }

    /**
     * get a list of the records_data ID that will be adjudicated
     *
     * @return array
     */
    public function getAdjudicated()
    {
        return $this->getProcessedIdList(self::FLAG_ADJUDICATED);
    }
    
    /**
     * parse data to build a record a record
     * 
     * @param mixed $record_id
     * @return void
     */
    public function parse($records_data)
    {
        $logs = []; // local logs
        
        // loop through every record data associated to the current record ID
        foreach($records_data as $config) {

            $record_data = new RecordData($this->project, $config);
            // starting to process
            $adjudication_id = $record_data->getID();

            // collect data from the record
            $record_id = $record_data->record;
            $value = $record_data->getValue();
            $event_id = $record_data->event_id;
            $field_name = $record_data->field_name;
            $temporal_field = $record_data->temporal_field;
            // get the name of the preselect strategy
            $preselect_strategy_name = $record_data->preselect;
            $record_path = compact('record_id','event_id','field_name'); // partial record path with no instance (for logs)

            if(trim($value)==='') {
                // skip the record if the value is empty
                $logs[] = self::createLogEntry($record_path, $record_data, "contains an empty value. skipping");
                // skip empty values
                $this->flagRecordData($adjudication_id, self::FLAG_EMPTY);
                continue;
            }
            
            // each record data target multiple instances: get a list
            $instance_list = $record_data->getTargetInstances();
            if(empty($instance_list)) {
                // skip the record if there are no target isntances available
                $logs[] = self::createLogEntry($record_path, $record_data, "no target instances for this record. skipping");
                // exclude if there is no valid target
                $this->flagRecordData($adjudication_id, self::FLAG_NO_TARGETS);
                continue;
            }

            // compare the value to adjudicate with the one in every target instance
            foreach ($instance_list as $instance) {
                $record_path['instance'] = $instance; // set the instance in the record path
                // compare with existing values
                $existing_value = current($this->project->getFieldValue($record_id, $event_id, $field_name, $instance));

                // check if the value mathces the existing one after 'preselect' strategies have been applied
                if($value===$existing_value) {
                    $logs[] = self::createLogEntry($record_path, $record_data, "new value matches existing data; skipping");
                    // skip matching existing values
                    $this->flagRecordData($adjudication_id, self::FLAG_EXISTS);
                    continue;
                }

                if(empty($temporal_field)){
                    //non-temporal data
                    $logs[] = self::createLogEntry($record_path, $record_data, "adding non-temporal value '{$value}' to adjudication list.");
                    $this->setData($record_data, $record_path);
                }else {
                    $existing_date_time = $record_data->getTemporalFieldDateTime($instance);
                    $source_date_time = $record_data->getSourceDateTime();
                    $preselect_strategy = AdjudicationStrategyFactory::make($preselect_strategy_name , $existing_date_time);
                    if(!$preselect_strategy instanceof AdjudicationStrategy) {
                        throw new \Exception("There was an error creating the preselect strategy with the name '$preselect_strategy_name'.", 400);
                    }

                    $previous_record = $this->getPreviousRecord($record_path);
                    // get the previous parsed value
                    if(!$previous_record instanceof ComparisonElement) {
                        // set the existing value as '
                        $logs[] = self::createLogEntry($record_path, $record_data, "using existing data as 'previous_record'");
                        $previous_date_time = ($existing_value===false) ? null : $existing_date_time; // set a null date if existing value is null
                        $previous_record = new ComparisonElement($existing_value, $previous_date_time);
                    }

                    // apply the preselect strategy and compare previous selected record with the current one
                    $current_record = new ComparisonElement($value, $source_date_time, $adjudication_id);
                    $comparison_result = $preselect_strategy->compare($previous_record, $current_record);
                    $logs[] = self::createLogEntry($record_path, $record_data, "instance {$instance}, preselect strategy '".get_class($preselect_strategy)."'. preselect comparison result is {$comparison_result}");

                    $previous_value = $previous_record->getValue();
                    $previous_id = $previous_record->getID();
                    switch ($comparison_result) {
                        case 1: {
                            $logs[] = self::createLogEntry($record_path, $record_data, "the previous preselected value '{$previous_value}' (ID {$previous_id}) has been replaced with the new temporal value '{$value}'");
                            $this->flagRecordData($previous_id, self::FLAG_NOT_BEST_OPTION);
                            // switch previous selected record_data with the current since it is the best option
                            $this->setPreviousRecord($current_record, $record_path);
                            $this->setData($record_data, $record_path);
                            break;
                        }
                        case -1: {
                            $logs[] = self::createLogEntry($record_path, $record_data, "the previous preselected value '{$previous_value}' (ID {$previous_id}) is a better choiche compared to the new temporal value '{$value}'. skipping");
                            $this->flagRecordData($adjudication_id, self::FLAG_NOT_BEST_OPTION);
                            break;
                        }
                        case 0: {
                            $logs[] = self::createLogEntry($record_path, $record_data, "the previous preselected value '{$previous_value}' (ID {$previous_id}) matches the new temporal value '{$value}'. skipping");
                            $this->flagRecordData($adjudication_id, self::FLAG_NOT_BEST_OPTION);
                            break;
                        }
                    }
                }
            }
        }
    }

    /**
     * Undocumented function
     *
     * @param array $record_path
     * @param RecordData $record_data
     * @param string $message
     * @return void
     */
    protected static function createLogEntry($record_path, $record_data, $message)
    {
        $path = implode('/', $record_path);
        $path = $record_path;
        return compact('path', 'record_data', 'message');
    }

    /**
     * set a status flag for every adjudication as values are processed
     *
     * @param int $id
     * @param string $flag
     * @return void
     */
    private function flagRecordData($id, $flag)
    {
        if(is_null($id)) return;
        if($flag!==self::FLAG_ADJUDICATED) {
            // when group is not 'adjudicated' make sure there is not an existing entry in the adjudicated list
            $adjudicted = @$this->processed_list[self::FLAG_ADJUDICATED] ?: [];
            $adjudicated_offset = array_search($id, $adjudicted);
            if(is_numeric($adjudicated_offset)) array_splice($this->processed_list[self::FLAG_ADJUDICATED], $adjudicated_offset, 1);
        }
        $group = @$this->processed_list[$flag] ?: [];
        $offset = array_search($id, $group);
        if(!is_numeric($offset)) {
            // add a new entry only if not exists
            $this->processed_list[$flag][] = $id;
        }
    }

    // helper function to set data using a specific path and build the record to be saved
    protected function setData($record_data, $path)
    {
        $adjudication_id = $record_data->getID();
        $value = $record_data->getValue();   
        $record_id = @$path['record_id'];
        $event_id = @$path['event_id'];
        $field_name = @$path['field_name'];
        $instance = @$path['instance'];
        $this->record = $this->project->buildRecord($this->record, $value, $record_id, $event_id, $field_name, $instance);
        $this->flagRecordData($adjudication_id, self::FLAG_ADJUDICATED);
    }

    protected function setPreviousRecord($comparison_element, $path)
    {
        $current = &$this->data;
        while($key = current($path)) {
            $current = &$current[$key];
            next($path);
        }
        $current = $comparison_element;
    }

    // get a previous record if available
    protected function getPreviousRecord($path)
    {
        $current = &$this->data;
        while($key = current($path)) {
            $current = &$current[$key];
            next($path);
        }
        return $current;
    }

     
}
    