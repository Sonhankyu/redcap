<?php

namespace Vanderbilt\REDCap\Classes\Fhir\ClinicalDataPull\AutoAdjudication\Strategies;

use Vanderbilt\REDCap\Classes\Fhir\ClinicalDataPull\AutoAdjudication\ComparisonElement;

class Lowest  extends AdjudicationStrategy
{
  /**
   * compare values
   * the record with the lowest value is better
   *
   * @param ComparisonElement $a
   * @param ComparisonElement $b
   * @return int
   */
  public function compare($a,$b)
  {
    $value_a = @$a->getValue();
    $value_b = $b->getValue();
    if($value_a<$value_b) return -1;
    if($value_a==$value_b) return 0;
    if($value_a>$value_b) return 1;
  }
  
}