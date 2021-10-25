<?php

namespace Vanderbilt\REDCap\Classes\Fhir\ClinicalDataPull\AutoAdjudication\Strategies;

use Exception;
use Vanderbilt\REDCap\Classes\Fhir\ClinicalDataPull\AutoAdjudication\ComparisonElement;

class Earliest extends AdjudicationStrategy
{
  /**
   * compare values
   * the record with the earliest date is better
   * @param ComparisonElement $a
   * @param ComparisonElement $b
   * @return int
   */
  public function compare($a,$b)
  {
    $timestamp_a = $a->getDateTime();
    $timestamp_b = $b->getDateTime();
    if(!$timestamp_a && !$timestamp_b) throw new Exception('valid timestamps from the FHIR source data are needed to preselect a value', 1);
    if(!$timestamp_b) return -1; // a is the best option because contains a valid timestamp
    if(!$timestamp_a) return 1; // b is the best option because contains a valid timestamp
    
    if($timestamp_a<$timestamp_b) return -1;
    if($timestamp_a==$timestamp_b) return 0;
    if($timestamp_a>$timestamp_b) return 1;
  }
  
}