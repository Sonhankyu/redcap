<?php
namespace Vanderbilt\REDCap\Classes\Fhir\Endpoints\R4;

use Vanderbilt\REDCap\Classes\Fhir\Endpoints\EndpointIdentifier;

class ObservationSmokingHistory extends AbstractObservation
{

  public function getResourceIdentifier()
  {
    return EndpointIdentifier::OBSERVATION_SMOKING_HISTORY;
  }

  public function getSearchRequest($params=[])
  {
    $params['category'] = self::CATEGORY_SOCIAL_HISTORY;
    return parent::getSearchRequest($params);
  }

}