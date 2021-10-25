<?php

use Vanderbilt\REDCap\Classes\Fhir\FhirClient;
use Vanderbilt\REDCap\Classes\Fhir\TokenManager\FhirTokenManager;
use Vanderbilt\REDCap\Classes\Fhir\MappingHelper\FhirMappingHelper;
use Vanderbilt\REDCap\Classes\Fhir\MappingHelper\FhirMappingHelperSettings;
use Vanderbilt\REDCap\Classes\Fhir\SerializableException;

/**
 * @method void index()
 * @method string fhirTest()
 * @method string getTokens()
 */
class FhirMappingHelperController extends BaseController
{

    /**
     * instance of the model
     *
     * @var FhirMappingHelper
     */
    private $mapping_helper;
    private $project_id;
    private $username;
    private $user_id;

    public function __construct()
    {
        parent::__construct();
        $this->project_id = @$_GET['pid'];
        $this->username = USERID ?: null;
        if($this->username) $this->user_id = User::getUIIDByUsername($this->username);
        if(!$this->user_id) {
            $e = new SerializableException('No user has been specified', $code=400);
            $this->printJSON($e, $code);
        }
        $this->mapping_helper = new FhirMappingHelper($this->project_id, $this->user_id);
    }

    function measureExecutionTime($callable) {

        $start = microtime(true);
        $result = $callable();
        $time_elapsed_secs = microtime(true) - $start;
        return [$result, $time_elapsed_secs];
    }
    /**
     * dispatch a 'search' request to a FHIR endpoint
     *
     * @return void
     */
    public function getResources()
    {
        try {
            $start = microtime(true);

            $tokenManager = new FhirTokenManager($this->user_id);
            $fhir_client = new FhirClient($this->project_id, $tokenManager);
            $fhir_category = @$_GET['fhir_category'];
            $mrn = @$_GET['mrn'];
            $options = json_decode(@$_GET['options'], $assoc=true);
            $response = $this->mapping_helper->getResourceByMrn($fhir_client, $fhir_category, $mrn, $options);
            $time_elapsed_secs = microtime(true) - $start;
            
            $response['elapsed_time'] = $time_elapsed_secs;
            $this->printJSON($response, 200);
        } catch (\Exception $e) {
            $code = $e->getCode();
            $message = $e->getMessage();
            $response = [
                'is_error' => true,
                'message' => $message,
                'code' => $code,
            ];
            $this->printJSON($response, $code);
        }
    }

    public function getFhirRequest()
    {
        $tokenManager = new FhirTokenManager($this->user_id);
        $fhir_client = new FhirClient($this->project_id, $tokenManager);
        $relative_url = @$_GET['relative_url'];
        $options = json_decode(@$_GET['options'], true);
        try {
            $response = $this->mapping_helper->getCustomFhirResource($fhir_client, $relative_url, 'GET', $options);
            /* $response = [
                'relative_url' => $relative_url,
                'options' => $options,
            ]; */
            $this->printJSON($response, 200);
        } catch (\Exception $e) {
            $code = $e->getCode();
            $message = $e->getMessage();
            $response = [
                'is_error' => true,
                'message' => $message,
                'code' => $code,
            ];
            $this->printJSON($response, $code);
        }
    }

    public function exportCodes()
    {
        $codings = $_POST['codings'];
        if(!is_array($codings)) return;
        $lines = array_map(function($coding) {
            return sprintf("%s %s", $coding['code'], $coding['display']);
        }, $codings);
        FileManager::exportText($lines);
    }

    /**
     * get settings and parameters for the app
     *
     * @return void
     */
    public function getSettings()
    {
        $fhir_mapping_helper_settings = new FhirMappingHelperSettings($this->mapping_helper);
        $app_settings = $fhir_mapping_helper_settings->getAppSettings();
        $user_info = $fhir_mapping_helper_settings->getUserInfo();
        $project_info = $fhir_mapping_helper_settings->getProjectInfo();
        $data = compact('app_settings', 'user_info', 'project_info');
        $this->printJSON($data, $status_code=200);
    }

    /**
     * send a notification to an admin
     * when a user wants to add a code
     * to those available in REDCap
     * admin will add the codes in Resources/misc/loinc_labs_additions.csv
     * @return void
     */
    public function notifyAdmin()
    {
        global $lang, $project_contact_email;
        $project_id = $this->project_id;
        $user = \User::getUserInfo($this->user_id);
        $user_email = $user['user_email'];
        $user_fullname = sprintf("%s %s", $user->user_firstname, $user->user_lastname);
        $project = new \Project($project_id);
        $project_admin_email = $project->project['project_contact_email'];

        $code = $_POST['code'];
        $resource_type = $_POST['resource_type'];
        $interaction = $_POST['interaction'];
        $mrn = $_POST['mrn'];

        /**
         * send an email
         */
        $email = new \Message();
        $emailSubject = "[REDCap] Request to insert a new FHIR code";
        $email->setFrom($user_email);
        $email->setFromName($GLOBALS['user_firstname']." ".$GLOBALS['user_lastname']);
        $to = array($project_contact_email, $project_admin_email);
        $email->setTo(implode(';', $to));
        $body = \Renderer::run('mapping-helper.emails.request-code', compact('lang', 'emailSubject','user_email','user_fullname','project_id','code','resource_type','interaction','mrn'));
        // Finalize email
        $email->setBody($body);
        $email->setSubject($emailSubject);
        
        if($email_sent = $email->send())
        {
            $response = array(
                'message'=>'email sent.',
                'code' => $code,
                'userid' => $this->user_id,
                'project_id' => $project_id,
                'project' => $project,
            );
            $this->printJSON($response, $status_code=200);
        }else {
            $response = array('message'=>'error sending the email.');
            $this->printJSON($response, $status_code=400);
        }
        
    }

    public function index()
    {
        extract($GLOBALS);
        include APP_PATH_DOCROOT . 'ProjectGeneral/header.php';
        $browser_supported = !$isIE || vIE() > 10;
        // $dist_path = APP_PATH_DOCROOT.'Resources/js/mapping-helper/dist';
        $app_path_js = APP_PATH_JS; // path to the JS folder
        print \Renderer::run('mapping-helper.index', compact('browser_supported', 'lang', 'app_path_js'));
        include APP_PATH_DOCROOT . 'ProjectGeneral/footer.php';
    }

}