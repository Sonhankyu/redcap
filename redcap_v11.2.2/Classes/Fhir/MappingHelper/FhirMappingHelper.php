<?php
namespace Vanderbilt\REDCap\Classes\Fhir\MappingHelper;

use Vanderbilt\REDCap\Classes\Fhir\DataMart\DataMartRevision;
use Vanderbilt\REDCap\Classes\Fhir\FhirClient;
use Vanderbilt\REDCap\Classes\Fhir\Endpoints\AbstractEndpoint;
use Vanderbilt\REDCap\Classes\Fhir\Resources\AbstractResource;

class FhirMappingHelper
{
    private $project_id;
    private $user_id;
    /**
     *
     * @param integer $project_id
     * @param integer $user_id
     */
    public function __construct($project_id, $user_id)
    {
        $this->project_id = $project_id;
        $this->user_id = $user_id;
    }

    /**
     * @return integer
     */
    public function getProjectId()
    {
        return $this->project_id;
    }

    /**
     * @return integer
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * print the link button pointing to the Mapping Helper page
     *
     * @param integer $project_id
     * @return void
     */
    public static function printLink($project_id)
    {
        $link = self::getLink($project_id);
        $html = sprintf('<a class="btn btn-primaryrc btn-xs" style="color:#fff !important;" href="%s">Mapping Helper</a>', $link);

        print $html;
    }

    /**
     * print the link button pointing to the Mapping Helper page
     *
     * @param integer $project_id
     * @return void
     */
    public static function getLink($project_id)
    {
        $parseUrl = function($URL) {
            $parts = parse_url($URL);
            $base = sprintf("%s://%s",@$parts['scheme'], @$parts['host']);
            return $base;
        };
        $root = $parseUrl(APP_PATH_WEBROOT_FULL);
        $version_dir = APP_PATH_WEBROOT;
        $url = $root.$version_dir."index.php?pid={$project_id}&route=FhirMappingHelperController:index";
        $double_slashes_regexp = "#(?<!https:)(?<!http:)\/\/#";
        $link = preg_replace($double_slashes_regexp, '/', $url);
        return $link;
    }

    /**
     *
     * @param Project $project
     * @return DataMartRevision|false
     */
    public function getDatamartRevision($project)
    {
        $datamart_enabled = boolval($project->project['datamart_enabled']);
        if(!$datamart_enabled) return false;
        $active_revision = DataMartRevision::getActive($project->project_id);
        return $active_revision;
    }

    public function getClinicalDataPullMapping()
    {
        $query_string = sprintf(
            'SELECT * FROM redcap_ddp_mapping
            WHERE project_id = %u', $this->project_id
        );
        $result = db_query($query_string);
        $mapping = array();
        while($row = db_fetch_assoc($result))
        {
            $mapping[] = $row;
        }
        return $mapping;
    }

    /**
     * Undocumented function
     *
     * @param FhirClient $fhir_client
     * @param string $fhir_category
     * @param string $mrn
     * @param array $options
     * @return FhirResource
     */
    public function getResourceByMrn($fhir_client, $fhir_category, $mrn, $options=[])
    {
        $patient_id = $fhir_client->getPatientID($mrn);
        if(!$patient_id) throw new \Exception("Patient ID not found", 404);
        
        $endpoint_factory = $fhir_client->getEndpointFactory();
        $endpoint = $endpoint_factory->makeEndpoint($fhir_category);
        if(!($endpoint instanceof AbstractEndpoint)) {
            throw new \Exception(sprintf('No endpoint available for the category %s', $fhir_category), 1);
        };
        $options_visitor = new EndpointOptionsVisitor($patient_id, $options, $fhir_client);
        $params = $endpoint->accept($options_visitor);
        $request = $endpoint->getSearchRequest($params);
        
        $response = [];
        if($request) {
            $resource = $fhir_client->getResource($request);
            // $fhir_code = $fhir_client->getFhirVersionManager()->getFhirCode();
            $resource_visitor = new ResourceVisitor();
            $data = $resource->accept($resource_visitor);
            $response['data'] = $data;
            $response['metadata'] = $resource->getMetadata();
        }
        return $response;
    }

    /**
     *
     * @param FhirClient $fhir_client
     * @param string $relative_url
     * @param string $method
     * @param array $options
     * @return AbstractResource
     */
    public function getCustomFhirResource($fhir_client, $relative_url, $method='GET', $options=[] )
    {   
        $queryOptions = ['query'=>$options];
        $fhir_request = $fhir_client->getFhirRequest($relative_url, $method, $queryOptions);
        $resource = $fhir_client->getResource($fhir_request);
        return $resource;
    }


    /**
     * get a list of codes that are available in REDCap, but not used
     *
     * @return void
     */
    public function getBlocklistedCodes()
    {
        $list = array();
        // Vital signs
        $list[] = new BlocklistCode('8716-3','too generic');
        return $list;
    }
}