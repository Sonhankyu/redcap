<?php
namespace Vanderbilt\REDCap\Classes\Fhir\Endpoints\R4;

use Vanderbilt\REDCap\Classes\Fhir\Endpoints\AbstractEndpoint;
use Vanderbilt\REDCap\Classes\Fhir\Endpoints\EndpointIdentifier;

/**
 * Undocumented class
 */
class ConditionProblems extends AbstractEndpoint
{

  const CLINICAL_STATUS_ACTIVE = 'active';
  const CLINICAL_STATUS_RECURRENCE = 'recurrence';
  const CLINICAL_STATUS_RELAPSE = 'relapse';
  const CLINICAL_STATUS_INACTIVE = 'inactive';
  const CLINICAL_STATUS_REMISSION = 'remission';
  const CLINICAL_STATUS_RESOLVED = 'resolved';
  const CATEGORY = 'problem-list-item';

  public function getResourceIdentifier()
  {
    return EndpointIdentifier::CONDITION_PROBLEMS;
  }

  public function getSearchRequest($params=[])
  {
    $params['category'] = self::CATEGORY;
    return parent::getSearchRequest($params);
  }

}