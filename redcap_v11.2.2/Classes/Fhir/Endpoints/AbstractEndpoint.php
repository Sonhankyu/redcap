<?php
namespace Vanderbilt\REDCap\Classes\Fhir\Endpoints;

use Vanderbilt\REDCap\Classes\Fhir\Endpoints\Traits\CanRemoveExtraSlashesFromUrl;

abstract class AbstractEndpoint implements EndpointInterface
{
  use CanRemoveExtraSlashesFromUrl;

  /**
   * interaction types
   * 
   * @see https://www.hl7.org/fhir/overview-dev.html#Interactions
   */
  const INTERACTION_READ = 'read';
  const INTERACTION_UPDATE = 'update';
  const INTERACTION_DELETE = 'delete';
  const INTERACTION_CREATE = 'create';
  const INTERACTION_SEARCH = 'search';
  const INTERACTION_HISTORY = 'history';
  const INTERACTION_TRANSACTION = 'transaction';
  const INTERACTION_OPERATION = 'operation';
  const INTERACTIONS = [
    self::INTERACTION_READ,
    self::INTERACTION_UPDATE,
    self::INTERACTION_DELETE,
    self::INTERACTION_CREATE,
    self::INTERACTION_SEARCH,
    self::INTERACTION_HISTORY,
    self::INTERACTION_TRANSACTION,
    self::INTERACTION_OPERATION,
  ];

  /**
   * part of the FHIR URL template
   * that identifies the resource type
   *
   * @var string
   */
  private $resource_identifier = null;

  /**
   *
   * @var string
   */
  private $base_url;

  public function __construct($base_url)
  {
    $this->base_url = $base_url;
  }

  public function getBaseUrl()
  {
    return $this->base_url;
  }

  public function getReadRequest($id)
  {
    $method = 'GET';
    $resource_identifier = $this->getResourceIdentifier();
    $URL = $this->removeExtraSlashesFromUrl(sprintf("%s/%s/%s", $this->base_url, $resource_identifier, $id));
    return new FhirRequest($URL, $method);
  }

  public function getSearchRequest($params=[])
  {
    $method = 'GET';
    $resource_identifier = $this->getResourceIdentifier();
    $URL = $this->removeExtraSlashesFromUrl(sprintf("%s/%s", $this->base_url, $resource_identifier));
    $options = ['query'=> $params];
    return new FhirRequest($URL, $method, $options);
  }

  /**
   * Undocumented function
   *
   * @param EndpointVisitorInterface $visitor
   * @return mixed
   */
  public function accept($visitor)
  {
    return $visitor->visit($this);
  }

}