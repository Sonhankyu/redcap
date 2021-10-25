<?php
namespace Vanderbilt\REDCap\Classes\Fhir\Resources\Shared;

use Vanderbilt\REDCap\Classes\Fhir\Endpoints\FhirRequest;
use Vanderbilt\REDCap\Classes\Fhir\Resources\AbstractResource;
use Vanderbilt\REDCap\Classes\Fhir\Resources\ResourceFactoryInterface;

class Bundle extends AbstractResource
{

  private $entries;
  /**
   * Resource Factory
   *
   * @var ResourceFactoryInterface
   */
  private $resourceFactory;

  /**
   *
   * @param Object $payload
   * @param ResourceFactoryInterface $resource_factory
   */
  public function __construct($payload, $resource_factory)
  {
    $this->resourceFactory = $resource_factory;
    parent::__construct($payload);
  }


  /**
   * generator that creates requests for next pages
   *
   * @return FhirRequest
   */
  public function getNextRequest()
  {
    $url = $this->query()
      ->select('#/link/\d+')
      ->where('relation','=','next')
      ->select('url')
      ->results()
      ->single();
    if(empty($url)) return false;
    $method = 'GET';
    $request = new FhirRequest($url, $method);
    return $request;
  }

  public function hasMoreEntries()
  {
    return $this->getNextRequest()!==false;
  }

  /**
   * create a list of resources based on the bundle
   * entry list
   *
   * @return AbstractResource[]
   */
  public function getEntries()
  {
    if(!isset($this->entries)) {
      $this->entries = [];
      $entriess_payload = $this->query()->select('#/entry/\d+/resource$')->results()->expand();
      foreach ($entriess_payload as $entry_payload) {
        $entry = $this->resourceFactory->make($entry_payload);
        if($entry) $this->entries[] = $entry;
      }
    }
    return $this->entries;
  }

  public function getMetaData()
  {
    $metadata = parent::getMetadata();
    $metadata['next_page'] = $this->getNextRequest();
    return $metadata;
  }

  public function getData()
  {
    $data = [
      'entry' => $this->getEntries(),
    ];
    return $data;
  }
  
}