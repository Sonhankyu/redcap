<?php

use Vanderbilt\REDCap\Classes\Fhir\ClinicalDataPull\AutoAdjudicator;

class CdisController extends BaseController
{


    public function __construct()
    {
        parent::__construct();
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
     * get the logs
     *
     * @return void
     */
    public function getCdpAutoAdjudicationLogs()
    {
        try {
            $project_id = $_GET['pid'];
            $start = intval($_GET['_start']) ?: 0;
            $limit = intval($_GET['_limit']) ?: 0;

            $logs = AutoAdjudicator::getLogsForProject($project_id, $start, $limit);
            $this->printJSON($logs, $status_code=200);
        } catch (\Exception $e) {
            //throw $th;
            $error = new JsonError(
                $title = 'error ritrieving logs',
                $detail = sprintf("There was a problem retrieving the logs for project ID %s", $project_id),
                $status = 400,
                $source = PAGE // get the current page
            );
            $this->printJSON($error, $status_code=400);
        }
    }

    /**
     * display a page with auto-adjudication logs
     *
     * @return void
     */
    public function showLogsPage()
    {
        global $lang, $project_id;
        extract($GLOBALS);
        include APP_PATH_DOCROOT . 'ProjectGeneral/header.php';
        $browser_supported = !$isIE || vIE() > 10;
        // $dist_path = APP_PATH_DOCROOT.'Resources/js/mapping-helper/dist';
        $app_path_js = APP_PATH_JS; // path to the JS folder
        $blade = Renderer::getBlade();
        $blade->share('browser_supported', $browser_supported);
        $blade->share('app_path_js', $app_path_js);
        $blade->share('lang', $lang);
        try {
            $logs = AutoAdjudicator::getLogsForProject($project_id);
            $blade->share('logs', $logs);
        } catch (\Exception $e) {
            $blade->share('error', $e->getMessage());
        }
        $view_variables = array();
        print $blade->run('cdp.auto-adjudication.logs', $view_variables);
        include APP_PATH_DOCROOT . 'ProjectGeneral/footer.php';
    }

}