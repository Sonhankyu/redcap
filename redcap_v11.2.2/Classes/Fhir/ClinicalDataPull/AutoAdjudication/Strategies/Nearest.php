<?php

namespace Vanderbilt\REDCap\Classes\Fhir\ClinicalDataPull\AutoAdjudication\Strategies;

use DateTime;
use Vanderbilt\REDCap\Classes\Fhir\ClinicalDataPull\AutoAdjudication\ComparisonElement;

class Nearest  extends AdjudicationStrategy
{
  use CanTransformTimestamps;


  /**
   * Date object to use as comparison
   * 
   * @var DateTime
   */
  private $reference_date;

  /**
   *
   * @param DateTime $reference_date
   */
  public function __construct($reference_date)
  {
    if(!$reference_date instanceof DateTime) throw new \Exception("A valid reference date is needed to use the 'nearest' comparison strategy. '{$reference_date}' was provided.", 400);
    $this->reference_date = $reference_date;
  }

  /**
   * calculate the absolute time difference between
   * the reference date and the provided date
   *
   * @param DateTime $date_time
   * @return DateInterval
   */
  protected function getAbsoluteTimeDifference($date_time)
  {
    $absolute = true;
    $absolute_diff = $this->reference_date->diff($date_time, $absolute);
    return $absolute_diff;
  }

  /**
   * compare values
   * the record with the nearest date is better
   *
   * @param ComparisonElement $a
   * @param ComparisonElement $b
   * @return int
   */
  public function compare($a, $b)
  {
    $timestamp_a = $a->getDateTime();
    $timestamp_b = $b->getDateTime();
    if(!$timestamp_a && !$timestamp_b) throw new \Exception('valid timestamps from the FHIR source data are needed to preselect a value', 1);
    if(!$timestamp_b) return -1; // a is the best option because contains a valid timestamp
    if(!$timestamp_a) return 1; // b is the best option because contains a valid timestamp

    $absolute_diff_a = $this->getAbsoluteTimeDifference($timestamp_a);
    $absolute_diff_b = $this->getAbsoluteTimeDifference($timestamp_b);
    
    if($absolute_diff_a==$absolute_diff_b) return 0;
    if($absolute_diff_a<$absolute_diff_b) return -1;
    if($absolute_diff_a>$absolute_diff_b) return 1;
  }
  
}