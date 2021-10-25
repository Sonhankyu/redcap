<?php

namespace Vanderbilt\REDCap\Classes\Fhir\ClinicalDataPull\AutoAdjudication\Strategies;

use Vanderbilt\REDCap\Classes\Fhir\ClinicalDataPull\AutoAdjudication\ComparisonElement;

interface AdjudicationStrategyInterface
{
  /**
   * compare two values
   * The comparison function must return
   * an integer less than, equal to, or greater
   * than zero if the first argument is considered
   * to be respectively less than, equal to, or
   * greater than the second.
   * 
   * A greater value is more relevant for the adjudication
   *
   * @param ComparisonElement $item_a a record to be adjudicated
   * @param ComparisonElement $item_b a record to be adjudicated
   * @return int 
   */
  public function compare($item_a, $item_b);
}