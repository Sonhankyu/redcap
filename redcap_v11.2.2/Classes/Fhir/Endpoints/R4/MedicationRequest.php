<?php
namespace Vanderbilt\REDCap\Classes\Fhir\Endpoints\R4;

use Vanderbilt\REDCap\Classes\Fhir\Endpoints\AbstractEndpoint;
use Vanderbilt\REDCap\Classes\Fhir\Endpoints\EndpointIdentifier;

class MedicationRequest extends AbstractEndpoint
{

  public function getResourceIdentifier()
  {
    return EndpointIdentifier::MEDICATION_REQUEST;
  }

  /**
   * convert REDCap medication mapping to a 
   * FHIR compatible param
   * @param array $fields
   * @return array
   */
  public function getStatusParam($fields=[])
  {
    $status_list = [];
    $status_reg_exp = '/^(?<status>active|completed|on-hold|stopped)-medications-list$/i';
    foreach ($fields as $field) {
      preg_match($status_reg_exp, $field, $matches);
      if($status_type = @$matches['status']) $status_list[] = $status_type;
    }
    $status = implode(',', $status_list);
    return $status;
  }

}