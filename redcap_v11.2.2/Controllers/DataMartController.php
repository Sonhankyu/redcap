<?php

use Vanderbilt\REDCap\Classes\Fhir\DataMart\DataMart;
use Vanderbilt\REDCap\Classes\Fhir\DataMart\DataMartBackgroundRunner;
use Vanderbilt\REDCap\Classes\Fhir\FhirLauncher;
use Vanderbilt\REDCap\Classes\Fhir\SerializableException;
use Vanderbilt\REDCap\Classes\Fhir\Utility\FileCache;

class DataMartController extends BaseController {

    /**
     * maximum number of simultaneous revisions per hour
     */
    const MAX_REVISIONS_PER_HOUR = 10;

    /**
     * instance of the model
     *
     * @var DataMart
     */
    private $model;

    /**
     * caching system
     *
     * @var FileCache
     */
    private $fileCache;

    public function __construct()
    {
        parent::__construct();
        $username = USERID ?: null;
        $userid = User::getUIIDByUsername($username);
        // $this->enableCORS();
        $this->model = new DataMart($userid);
        $project_id = $_REQUEST['pid'] ?: 0;
        $this->fileCache = new FileCache('DataMartController'.$project_id);
    }

    /**
     * route, get a list of revisions
     *
     * @return string json response
     */
    public function revisions()
    {
        $project_id = isset($_REQUEST['pid']) ? $_REQUEST['pid'] : null;
        if(isset($_REQUEST['request_id']) && $request_id = $_REQUEST['request_id'])
        {
            $revision = $this->model->getRevisionFromRequest($request_id);
            if(!$revision)
            {
                $error = new JsonError(
                    $title = 'revision not found',
                    $detail = sprintf("no revision associated to the request ID %s has been found", $request_id),
                    $status = 400,
                    $source = PAGE // get the current page
                );
                $this->printJSON($error, $status_code=400);
            }
            $response = array($revision);
        }else
        {
            $response = $this->model->getRevisions($project_id);
        }
        $this->printJSON($response, $status_code=200);
    }

    /**
     * route, get the user
     *
     * @return string json response
     */
    public function getUser()
    {
        global $userid;
        /* 
         * static version
        $modelClassName = get_class($this->model);
        $response =   call_user_func_array(array($modelClassName, "getUserInfo"), array($this->username, $this));
        */

		$project_id = isset($_REQUEST['pid']) ? $_REQUEST['pid'] : null;
        $response =   $this->model->getUser($project_id);
        $this->printJSON($response, $status_code=200);
    }

    /**
     * add a revision
     *
     * @return string
     */
    public function addRevision()
    {
        $settings = array(
            'project_id'    => isset($_REQUEST['pid']) ? $_REQUEST['pid'] : null,
            'request_id'    => isset($_REQUEST['request_id']) ? $_REQUEST['request_id'] : null,
            'mrns'          => isset($_REQUEST['mrns']) ? $_REQUEST['mrns'] : null,
            'fields'        => $_REQUEST['fields'],
            'date_min'      => $_REQUEST['date_min'],
            'date_max'      => $_REQUEST['date_max'],
        );
        $response = $this->model->addRevision($settings);
        if($response==true)
            $this->printJSON($response, $status_code=200);
        else
            $this->printJSON($response, $status_code=400);
    }

    /**
     * delete a revision
     *
     * @return void
     */
    public function deleteRevision()
    {
        // gete the data from the DELETE method
        $data = file_get_contents("php://input");
        $params = json_decode($data);
        $id = $params->revision_id;
        $deleted = $this->model->deleteRevision($id);
        if($deleted==true)
        {
            $response = array('data'=>array('id'=>$id));
            $this->printJSON($response, $status_code=200);
        } else
        {
            // typical structure for a json object
            $error = new JsonError(
                $title = 'Revision not deleted',
                $detail = sprintf("The revision ID %u could not be deleted.", $id ),
                $status = 400,
                $source = PAGE
            );
            $this->printJSON($error, $status_code=400);
        }
    }
    /**
     * export a revision
     *
     * @return void
     */
    public function exportRevision()
    {
        $revision_id = $_REQUEST['revision_id'];
        $format = isset($_REQUEST['format']) ? $_REQUEST['format'] : 'csv';
        $csv_delimiter = isset($_REQUEST['csv_delimiter']) ? $_REQUEST['csv_delimiter'] : User::getCsvDelimiter();
        $fields = isset($_REQUEST['fields']) ? $_REQUEST['fields'] : array();
        $this->model->exportRevision($revision_id, $fields, $format, $csv_delimiter);
    }

    /**
     * parse a file for a revision
     *
     * @return string
     */
    public function importRevision()
    {
        $uploaded_files = FileManager::getUploadedFiles();
        $files = $uploaded_files['files'];
        $file = reset($files); // get the first element in the array of files
        if($file)
        {
            $data = $this->model->importRevision($file);
            $this->printJSON($data, $status_code=200);
        }else
        {
            $error = new JsonError(
                $title = 'no file to process',
                $detail = 'A file must be provided to import a revision',
                $status = 400,
                $source = PAGE // get the current page
            );
            $this->printJSON($error, $status_code=400);
        }
    }

    public function getSettings()
    {
        global $lang;
        try {
            $project_id = defined('PROJECT_ID') ? PROJECT_ID : intval(@$_GET['pid']);
            $user_id = defined('USERID') ? USERID : false;
    
            $settings = $this->model->getSettings($project_id, $lang);
            $this->printJSON($settings, $status_code=200);
        } catch (\Exception $e) {
            $code = $e->getCode();
            if($code<400) $code = 400;
            $this->printJSON($e->getMessage(), $code);
        }
    }

    /**
     * helper function that sends an error response if the maximum
     * number of requests for a page has been reached
     *
     * @param integer $limit
     * @return string|null
     */
    public function throttle($limit=10)
    {
        $page = PAGE; // get the current page
        $throttler = new Throttler();
        
        if($throttler->throttle($page, $limit))
        {
            // typical structure for a json object
            $error = new JsonError(
                $title = 'Too Many Requests',
                $detail = sprintf('The maximum of %u simultaneus request%s has been reached. Try again later.', $limit, $singular=($limit===1) ? '' : 's' ),
                $status = Throttler::ERROR_CODE,
                $source = PAGE
            );

            $this->printJSON($error , $status_code=$status);
        }
    }

    /**
     * method for testing the throttle
     *
     * @return string
     */
    private function throttleTest()
    {
        $this->throttle(1); //limit to a maximum of 1
        sleep(10);
        $this->printJSON(array('success' => true, 'errors'=>array()), $status_code=200);
    }

    /**
     * run a revision
     *
     * @return string
     */
    public function runRevision()
    {
        $runBackgroundProcess = function($revisionId, $mrn_list) {
            try {
                $message = 'The request has been queued and will be run in a background process.';
                $send_feedback = json_decode(@$_POST['send_feedback']);
                if($send_feedback) $message .= PHP_EOL.'You will receive a message when the process is completed.';
                $bgRunner = new DataMartBackgroundRunner($this->model);
                $bgRunner->schedule($revisionId, $mrn_list, $send_feedback);
                $response = [
                    'success' => true,
                    'message' => $message,
                ];
                $this->printJSON($response, $status_code=200);
            } catch (\Exception $e) {
                $exception = new SerializableException($e->getMessage(), $code=$e->getCode());
                $this->printJSON($exception, $code);
            }
            
        };
        $this->throttle(self::MAX_REVISIONS_PER_HOUR);
        try {
            $revisionId = @$_POST['revision_id'];
            $mrn = @$_POST['mrn'];
            $mrn_list = @$_POST['mrn_list'];
            $background = json_decode(@$_POST['background']);
            if($background) return $runBackgroundProcess($revisionId, $mrn_list);

            $response = $this->model->runRevision($revisionId, $mrn);
            $this->printJSON($response, $status_code=200);
        } catch (\Exception $e) {
            $exception = new SerializableException($e->getMessage(), $code=$e->getCode());
            $this->printJSON($exception, $code);
        }
    }

    /**
     * approve a revision
     *
     * @return string
     */
    public function approveRevision()
    {
        $id = $_REQUEST['revision_id'];
        $revision = $this->model->approveRevision($id);
        if($revision)
        {
            $response = array('data'=>$revision);
            $this->printJSON($response, $status_code=200);
        }else
        {
            $error_code = 401; //unauthorized
            $error = new JsonError(
                $title = 'Revision not approved',
                $detail = sprintf("The revision ID %u could not be approved.", $id),
                $status = $error_code,
                $source = PAGE
            );
            $this->printJSON($error, $status_code=$error_code);
        }
    }

    public function index()
    {
        extract($GLOBALS);
        include APP_PATH_DOCROOT . 'ProjectGeneral/header.php';
        $browser_supported = !$isIE || vIE() > 10;
        $datamart_enabled = DataMart::isEnabled($project_id);
        $app_path_js = APP_PATH_JS; // path to the JS directory
		// generate CSS and javascript tags
        $blade = Renderer::getBlade();
		$blade->share('app_path_js', $app_path_js);
        print $blade->run('datamart.index', compact('browser_supported', 'datamart_enabled'));
        include APP_PATH_DOCROOT . 'ProjectGeneral/footer.php';
    }

    public function searchMrns()
    {
        $project_id = $_REQUEST['pid'] ?: 0;
        $query = @$_GET['query'];
        $start = @$_GET['start'] ?: 0;
        $limit = @$_GET['limit'] ?: 0;
        $result = $this->model->searchMrns($project_id, $query, $start, $limit);
        $this->printJSON($result);

    }


}