<?php
namespace Vanderbilt\REDCap\Classes\Fhir\FhirStats;

/**
 * @property string|string[] $type
 * @property boolean|boolean[] $adjudicated
 * @property string|DateTime $date_start
 * @property string|DateTime $date_end
 */
    class FhirStats
{
    /**
     * search parameters to retrieve data from the database
     *
     * @var array
     */
    private $search_params = [];

    /**
     * set the search parameters upon construction
     *
     * @param array $params
     */
    public function __construct($params=[])
    {
        foreach($params as $key => $value)
        {
            $this->search_params[$key] = $value;
        }
    }

    /**
     * get the search parameters provided on creation
     *
     * @return array
     */
    public function getSearchParameters()
    {
        return $this->search_params;
    }

    /**
     * get all user with Clinical Data Mart privileges
     *
     * @param boolean $include_super_user wheter to include or not super admins in the list
     * @return array
     */
    public function getCdmUsers($include_super_user=true)
    {
        // compose the query string
        $query_string = "SELECT * FROM redcap_user_information
                        WHERE (fhir_data_mart_create_project=1 AND super_user=0)";
        if($include_super_user) $query_string .= " OR super_user=1";
        $result = db_query($query_string);
        $rows = [];
        while($row = db_fetch_assoc($result)) $rows[] = $row;
        return $rows;
    }

    /**
     * helper function to add quotes to a string
     *
     * @param string $str
     * @return string
     */
    public function addQuotes($str) {
        return sprintf("'%s'", $str);
    }

    /**
     * get the SUM portion of the query
     * to count FHIR resource's entries.
     *
     * @return string
     */
    private function getCountQuery()
    {
        $date_start = $this->date_start;
        $date_end = $this->date_end;
        // make sure dates are converted to DateTime
        if($date_start && !$date_start instanceof \DateTime) $date_start = \DateTime::createFromFormat('Y-m-d', $date_start);
        if($date_end && !$date_end instanceof \DateTime) $date_end = \DateTime::createFromFormat('Y-m-d', $date_end);
        if($date_start) $date_start->setTime(0,0,0); // set time to beginning of day
        if($date_end) $date_end->setTime(23,59,59); // set time to end of day

        $query_string = '';
        $counts_query = []; // collect SUM statements for imploding later
        foreach (FhirStatsCollector::getResourceIdentifiers() as $resource_type) {
            $counts_query[] = sprintf("\nCOALESCE(SUM(`counts_%s`), 0) as `%s`", $resource_type, $resource_type);
        }
        $query_string .= implode(', ', $counts_query);
        $query_string .= sprintf(
            " FROM %s WHERE 1", FhirStatsCollector::COUNTS_TABLE_NAME
        );
        
        if($date_start) $query_string .= sprintf(" AND ts >= '%s'", $date_start->format('Y-m-d H:i:s'));
        if($date_end) $query_string .= sprintf(" AND ts <= '%s'", $date_end->format('Y-m-d H:i:s'));
        return $query_string;
    }


    /**
     * get SUM of all resources:
     *
     * @param array $type CDM or CDP
     * @return array
     */
    public function getTotal($type=[])
    {
        if(!is_array($type)) $type = [$type]; // make sure type is an array
        $query_string = "SELECT ".$this->getCountQuery();
        if(!empty($type)) $query_string .= sprintf(" AND type IN (%s)", implode(',', array_map([$this, 'addQuotes'], $type)));
        $result = db_query($query_string);
        $counts = [];
        if($row = db_fetch_assoc($result)) $counts = $row;
        return $counts;
    }

    /**
     * get the total per resource type grouped by day
     *
     * @param array $entries database entries
     * @return array
     */
    public function getCountsPerDay($type=[])
    {
        if(!is_array($type)) $type = array($type); // make sure type is an array
        $query_string = "SELECT ts, ".$this->getCountQuery();
        if(!empty($type)) $query_string .= sprintf(" AND type IN (%s)", implode(',', array_map([$this, 'addQuotes'], $type)));
        $query_string .= " GROUP BY DATE_FORMAT(`ts`, '%Y%m%d')"; // group by day
        $query_string .= " ORDER BY `ts` ASC"; // order by date
        $result = db_query($query_string);
        $counts_per_day = [];
        while($row = db_fetch_assoc($result))
        {
            $timestamp = $row['ts'];
            if(empty($timestamp)) continue; //day cannot be blank
            $date_time = new \DateTime($timestamp);
            $day = $date_time->format('m-d-Y');
            $counts = [];
            foreach (FhirStatsCollector::getResourceIdentifiers() as $resource) {
                $count = $row[$resource] ?: 0;
                $counts[$resource] = $count;
            }
            $counts_per_day[$day] = $counts;
        }
        return $counts_per_day;
    }

    /**
     * get a link to export data
     *
     * @param array $params search parameters
     * @return string
     */
    private function getExportLink($params)
    {
        $controllerName = array_pop(explode('\\', FhirStatsController::class));
        $query_params = array(
            'route' => $controllerName.":export"
        );
        $query_params = array_merge($query_params, $params);
        $export_link = APP_PATH_WEBROOT."index.php?".http_build_query($query_params);
        return $export_link;
    }

    /**
     * get all data related to CDP and CDM
     *
     * @return array
     */
    public function getCounts()
    {
        
        // get a list of CDM users
        $cdm_users = $this->getCdmUsers();
        // overall entries per day
        $results = [
            'data' => [
                'overall' => [
                    'total' => $this->getTotal([
                        FhirStatsCollector::REDCAP_TOOL_TYPE_CDM,
                        FhirStatsCollector::REDCAP_TOOL_TYPE_CDP,
                        FhirStatsCollector::REDCAP_TOOL_TYPE_CDP_INSTANT,
                    ]),
                    'daily' => $this->getCountsPerDay([
                        FhirStatsCollector::REDCAP_TOOL_TYPE_CDM,
                        FhirStatsCollector::REDCAP_TOOL_TYPE_CDP,
                        FhirStatsCollector::REDCAP_TOOL_TYPE_CDP_INSTANT,
                    ]),
                ],
                FhirStatsCollector::REDCAP_TOOL_TYPE_CDM => [
                    'total' => $this->getTotal(FhirStatsCollector::REDCAP_TOOL_TYPE_CDM),
                    'daily' => $this->getCountsPerDay(FhirStatsCollector::REDCAP_TOOL_TYPE_CDM),
                ],
                FhirStatsCollector::REDCAP_TOOL_TYPE_CDP => [
                    'total' => $this->getTotal(FhirStatsCollector::REDCAP_TOOL_TYPE_CDP),
                    'daily' => $this->getCountsPerDay(FhirStatsCollector::REDCAP_TOOL_TYPE_CDP),
                ],
                FhirStatsCollector::REDCAP_TOOL_TYPE_CDP_INSTANT => [
                    'total' => $this->getTotal(FhirStatsCollector::REDCAP_TOOL_TYPE_CDP_INSTANT),
                    'daily' => $this->getCountsPerDay(FhirStatsCollector::REDCAP_TOOL_TYPE_CDP_INSTANT),
                ],
                'cdm_users_count' => count($cdm_users),
            ],
            'metadata' => [
                'search_params' => $searchParams = $this->getSearchParameters(), // return the searched values as metadata
                'export_link' => $this->getExportLink($searchParams),
            ]
        ];
        return $results;
    }

    /**
     * get a data row with 'type' using entries retrieved from the database
     * the resulting array can be used as a CSV row
     *
     * @param array $counts
     * @param string $tool can be CDM or CDP
     * @return array
     */
    private function getCsvTotalCounts($counts, $tool)
    {
        $csv_data = ['Tool' => $tool];
        return array_merge($csv_data, $counts); // merge the tool with the counts
    }

    /**
     * get CSV rows with daily counts
     *
     * @param array $entries database entries
     * @return array
     */
    private function getCsvDailyCounts($daily_counts)
    {
        $csv_daily_counts = [];
        foreach($daily_counts as $day => $counts) {
            $csv_row = ['Day' => $day];
            $csv_daily_counts[] = array_merge($csv_row, $counts);
        }
        return $csv_daily_counts;
    }

    /**
     * export csv files to a zip archive
     *
     * @return void
     */
    public function exportData()
    {
        $overall_counts = $this->getTotal([
            FhirStatsCollector::REDCAP_TOOL_TYPE_CDM,
            FhirStatsCollector::REDCAP_TOOL_TYPE_CDP,
            FhirStatsCollector::REDCAP_TOOL_TYPE_CDP_INSTANT
        ]);
        $cdm_counts = $this->getTotal(FhirStatsCollector::REDCAP_TOOL_TYPE_CDM);
        $cdp_counts = $this->getTotal(FhirStatsCollector::REDCAP_TOOL_TYPE_CDP);
        $cdp_i_counts = $this->getTotal(FhirStatsCollector::REDCAP_TOOL_TYPE_CDP_INSTANT);

        // count total entries for each group and group them in a single array
        $csv_total_counts = [
            $this->getCsvTotalCounts($overall_counts, 'overall'),
            $this->getCsvTotalCounts($cdm_counts, 'cdm'),
            $this->getCsvTotalCounts($cdp_counts, 'cdp'),
            $this->getCsvTotalCounts($cdp_i_counts, 'cdp_i'),
        ];

        $daily_overall_counts = $this->getCountsPerDay([
            FhirStatsCollector::REDCAP_TOOL_TYPE_CDM,
            FhirStatsCollector::REDCAP_TOOL_TYPE_CDP,
            FhirStatsCollector::REDCAP_TOOL_TYPE_CDP_INSTANT,
        ]);
        $daily_cdm_counts = $this->getCountsPerDay(FhirStatsCollector::REDCAP_TOOL_TYPE_CDM);
        $daily_cdp_counts = $this->getCountsPerDay(FhirStatsCollector::REDCAP_TOOL_TYPE_CDP);
        $daily_cdp_i_counts = $this->getCountsPerDay(FhirStatsCollector::REDCAP_TOOL_TYPE_CDP_INSTANT);

        // collect data
        $csv_data = [
            'total_counts' => $csv_total_counts,
            'daily_overall_counts' => $this->getCsvDailyCounts($daily_overall_counts),
            'daily_cdm_counts' => $this->getCsvDailyCounts($daily_cdm_counts),
            'daily_cdp_counts' => $this->getCsvDailyCounts($daily_cdp_counts),
            'daily_cdp_instant_counts' => $this->getCsvDailyCounts($daily_cdp_i_counts),
        ];

        // Prepare File
        $file = tempnam("tmp", "zip");
        $zip = new \ZipArchive();
        $zip->open($file, \ZipArchive::OVERWRITE);
        foreach ($csv_data as $label => $data) {
            $csv = \FileManager::getCSV($data);
            $name = "{$label}.csv";
            $zip->addFromString($name, $csv);
        }

        // Close and send to output
        $zip->close();
        header('Content-Type: application/zip');
        header('Content-Length: ' . filesize($file));
        header('Content-Disposition: attachment; filename="fhir-statistics.zip"');
        readfile($file);
        unlink($file);
    }

    /**
     * magic getter for search parameters
     *
     * @param string $name
     * @return void
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->search_params))
        {
            return $this->search_params[$name];
        }

        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);
        return null;
    }

}