<?php

use Vanderbilt\REDCap\Classes\Fhir\FhirStats\FhirStats;

class FhirStatsController extends BaseController
{


    public function __construct()
    {
        parent::__construct();
    }

    /**
     * export data to CSV
     *
     * @return void
     */
    public function export()
    {
        $params = [
            'date_start' => @$_GET['date_start'] ?? '',
            'date_end' => @$_GET['date_end'] ?? '',
        ];
        $fhir_stats = new FhirStats($params);
        $fhir_stats->exportData();
    }


    public function getStats() {
		$params = [
            'date_start' => @$_GET['date_start'] ?? '',
            'date_end' => @$_GET['date_end'] ?? '',
        ];
        $fhir_stats = new FhirStats($params);
        $response = $fhir_stats->getCounts();
        $this->printJSON($response, 200);
    }
    
    public function index()
    {
        global $lang;
        if (!ACCESS_ADMIN_DASHBOARDS) redirect(APP_PATH_WEBROOT);
		extract($GLOBALS);


        include APP_PATH_DOCROOT . 'ControlCenter/header.php';
        
        $browser_supported = !$isIE || vIE() > 10;
        $data = compact('date_start','date_end','results','show','export_link','browser_supported');

        $blade = Renderer::getBlade(); // get an instance of the templating engine
        // share variables to make them available in sub-views
        $blade->share('lang', $lang);
        $blade->share('app_path_js', APP_PATH_JS);
        print $blade->run('control-center.fhir-stats.index', $data);
        include APP_PATH_DOCROOT . 'ControlCenter/footer.php';
    }

}