<?php

use Vanderbilt\REDCap\Classes\Pagination\Paginator;
use Vanderbilt\REDCap\Classes\Queue\Queue;
use Vanderbilt\REDCap\Classes\Queue\Worker;

class QueueController extends BaseController
{
    private function getMessages($limit, $start=null)
    {
        $getTotal = function($query) {
            $query_string = sprintf("SELECT COUNT(1) AS total FROM (%s) AS sub", $query);
            $result = db_query($query_string);
            if(!$result) return 0;
            if($row=db_fetch_assoc($result)) return intval(@$row['total']);
        };

        $limit = intval($limit) ?: 100;
        $start = intval($start) ?: 0;
        $query_string = sprintf(
            'SELECT `queue`.*, `log`.`description` AS `log_description`
            FROM %s AS `queue`
            LEFT JOIN (SELECT `description`, `pk` FROM `redcap_log_event` WHERE `object_type`=%s ORDER BY `ts` DESC LIMIT 1) AS `log`
            ON `log`.`pk`=`queue`.`id`
            ORDER BY `queue`.`id` DESC',
            Queue::TABLE_NAME, checkNull(Worker::LOG_OOBJECT_TYPE)
        );
        // get total without limit and start
        $total = $getTotal($query_string);
        // add limit
        $query_string .= sprintf(" LIMIT %u, %u", $start, $limit);
        $result = db_query($query_string);
        $messages = [];
        while ($row = db_fetch_assoc($result)) {
            $messages[] = $row;
        }
        return (object) [
            'items' => $messages,
            'total' => $total
        ];
    }



    public function index()
    {
        global $lang;
        if (!ACCESS_ADMIN_DASHBOARDS) redirect(APP_PATH_WEBROOT);

		extract($GLOBALS);
        include APP_PATH_DOCROOT . 'ControlCenter/header.php';

        $printStatus = function($status) {
            switch ($status) {
                case Queue::STATUS_READY:
                    $text = '<i class="fas fa-layer-group text-primary" title="'.$status.'"></i>';
                    break;
                case Queue::STATUS_PROCESSING:
                    $text = '<i class="fas fa-spinner fa-spin" title="'.$status.'"></i>';
                    break;
                case Queue::STATUS_COMPLETED:
                    $text = '<i class="fas fa-check-circle text-success" title="'.$status.'"></i>';
                    break;
                case Queue::STATUS_CANCELED:
                    $text = '<i class="fas fa-ban" title="'.$status.'"></i>';
                    break;
                case Queue::STATUS_ERROR:
                    $text = '<i class="fas fa-exclamation text-danger" title="'.$status.'"></i>';
                    break;
                default:
                    $text = '';
                    break;
            }
            return $text;
        };
        
        $browser_supported = !$isIE || vIE() > 10;
        $app_path_js = APP_PATH_JS; // path to the JS directory
		// generate CSS and javascript tags
        $blade = Renderer::getBlade();
		$blade->share('browser_supported', $browser_supported);
		$blade->share('printStatus', $printStatus);
        $blade->share('app_path_js', $app_path_js);
        $blade->share('lang', $lang);
        
        // set limit and start
        $page = intval(@$_GET['_page']) ?: 1;
        $per_page = intval(@$_GET['_per_page']) ?: 50;
        $start = ($page-1)*$per_page; // calculate the start for the query
        $result = $this->getMessages($per_page, $start);
        $messages = $result->items;
        // create a paginator
        $paginator_options = [
            'max_items' => 7,
            'path' => $_SERVER['REQUEST_URI'] ?: '/',
        ];
        $paginator = new Paginator($result->total, $per_page, $page, $paginator_options);
        $blade->share('paginator', $paginator);
        
        print $blade->run('queue.index', compact('messages'));
        include APP_PATH_DOCROOT . 'ControlCenter/footer.php';
    }
}