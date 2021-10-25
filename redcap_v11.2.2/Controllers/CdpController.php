<?php

use Vanderbilt\REDCap\Classes\Fhir\ClinicalDataPull\AutoAdjudication\AutoAdjudicator;
use Vanderbilt\REDCap\Classes\Fhir\ClinicalDataPull\Mapper as CdpMapper;

class CdpController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    private function getMapperInstance($project_id) {
        $config = System::getConfigVals();
        $mapper = new CdpMapper(new Project($project_id), $config);
        return $mapper;
    }

    /**
     * route, get a list of revisions
     *
     * @return string json response
     */
    public function test()
    {
        $response = array('test' => 123);
        $this->printJSON($response, $status_code=200);
    }

    /**
     * auto-adjudication
     * get stats about the data to be auto-adjudicated
     *
     * @return void
     */
    public function getDdpRecordsDataStats()
    {
        try {
            $project_id = @$_GET['pid'];
            $user_id = defined('USERID') ? USERID : false;
            $offset = @$_GET['offset']; // check if we are loading more data
            if(empty($project_id)) throw new \Exception("A project ID must be provided", 1);
            $adjudicator = new AutoAdjudicator($project_id, $user_id);
            $response = $adjudicator->getDdpRecordsMetadata($offset);
            $this->printJSON($response);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $status_code = $e->getCode();
            $this->printJSON($message, $status_code);
        }
    }

    /**
     * auto-adjudication
     * adjudicate all data found on database
     * for a project
     *
     * @return void
     */
    public function adjudicateCachedRecords()
    {
        try {
            $project_id = @$_GET['pid'];
            $user_id = defined('USERID') ? USERID : false;
            $background = @$_POST['background'];
            $send_feedback = @$_POST['send_feedback'];
            if(empty($project_id)) throw new \Exception("A project ID must be provided", 400);
            $adjudicator = new AutoAdjudicator($project_id, $user_id);
            $records = $adjudicator->adjudicateCachedRecords($background, $send_feedback);
            $this->printJSON($records);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $status_code = $e->getCode();
            $this->printJSON($message, $status_code);
        }
    }

    /**
     * auto-adjudication
     * adjudicate all data found on database
     * for a project and a specific record
     * @return void
     */
    public function adjudicateCachedRecord()
    {
        try {
            $project_id = @$_GET['pid'];
            $user_id = defined('USERID') ? USERID : false;
            $record_id = @$_POST['record_id'];
            // throw new \Exception("A project ID must be provided", 400);
            if(empty($project_id)) throw new \Exception("A project ID must be provided", 400);
            if(empty($record_id)) throw new \Exception("A record ID must be provided", 400);
            $adjudicator = new AutoAdjudicator($project_id, $user_id);
            $records = $adjudicator->adjudicateCachedRecord($record_id);
            $this->printJSON($records);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $status_code = $e->getCode();
            $this->printJSON($message, $status_code);
        }
    }
    
    public function getPreviewData()
    {
        $project_id = @$_GET['pid'];
        $record_identifier = @$_GET['record_identifier'];
        if(empty($project_id) || empty($record_identifier)) {
            $this->printJSON($message='A project ID and a record identifier must be provided', 400);
        }
        try {
            $ddp = new DynamicDataPull($project_id, $realtime_webservice_type='FHIR');
            $response = $ddp->getPreviewData($record_identifier);
            $this->printJSON($response, 200);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $status_code = $e->getCode();
            $this->printJSON($message, $status_code);
        }
    }

    /**
     * get the logs
     *
     * @return void
     */
    public function getSettings()
    {
        try {
            $project_id = $_GET['pid'];
            $mapper = $this->getMapperInstance($project_id);
            $settings = $mapper->getSettings();
            $this->printJSON($settings, $status_code=200);
        } catch (\Exception $e) {
            //throw $th;
            $error = new JsonError(
                $title = 'error ritrieving settings',
                $detail = sprintf("There was a problem retrieving the settings for project ID %s", $project_id),
                $status = 400,
                $source = PAGE // get the current page
            );
            $this->printJSON($error, $status_code=400);
        }
    }

    /**
     * get the logs
     *
     * @return void
     */
    public function setSettings()
    {
        try {
            $project_id = @$_GET['pid'];
            $settings = json_decode(@$_POST['settings'], $assoc=true);
            $mapping = json_decode(@$_POST['mapping'], $assoc=true);
            
            $mapper = $this->getMapperInstance($project_id);
            $settings_result = $mapper->saveSettings($settings);
            $mapping_result = $mapper->saveMapping($mapping);
            $response = compact('settings_result', 'mapping_result');
            $this->printJSON($response, $status_code=200);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $response = [
                'error' => true,
                'message' => $message,
            ];
            $status_code = 400;
            $this->printJSON($response, $status_code);
        }
    }

    /**
     * TODO
     *
     * @return void
     */
    public function importMapping()
    {
        try {
            $file = @$_FILES['file'];
            if(!$file) {
                $response = ['message' => 'no file received'];
                $this->printJSON($response, $status_code=200);
            }
            $file_path = @$file['tmp_name'];
            
            $project_id = @$_GET['pid'];
            $mapper = $this->getMapperInstance($project_id);
            $response = $mapper->importMapping($file_path);
            $this->printJSON($response, $status_code=200);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $response = [
                'error' => true,
                'message' => $message,
            ];
            $status_code = 400;
            $this->printJSON($response, $status_code);
        }

    }

    /**
     * TODO
     *
     * @return void
     */
    public function exportMapping()
    {
        /* $DDP = new DynamicDataPull(null, 'FHIR');
        $DDP_data_tool = new DDPDataTool($DDP);
        $filename_suffix = 'mapping-fields';
        $filename = isset($project_name) ? "{$project_name}-{$filename_suffix}" : $filename_suffix;
        $DDP_data_tool->exportMappingFields($filename); */
        try {
            $project_id = @$_GET['pid'];
            $mapper = $this->getMapperInstance($project_id);
            $download_url = $mapper->exportMapping();
            $response = compact('download_url');
            $this->printJSON($response, $status_code=200);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $response = [
                'error' => true,
                'message' => $message,
            ];
            $status_code = 400;
            $this->printJSON($response, $status_code);
        }
    }  
    
    public function download()
    {
        try {
            $project_id = @$_GET['pid'];
            $file_name = @$_GET['file_name'];
            $mapper = $this->getMapperInstance($project_id);
            return $mapper->download($file_name);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $response = [
                'error' => true,
                'message' => $message,
            ];
            $status_code = 400;
            $this->printJSON($response, $status_code);
        }
    }

}