<?php

namespace Vanderbilt\REDCap\Classes\Fhir\ClinicalDataPull\AutoAdjudication\Strategies;

use DateTime;

class AdjudicationStrategyFactory
{

  const STRATEGY_MIN = 'MIN';
  const STRATEGY_MAX = 'MAX';
  const STRATEGY_FIRST = 'FIRST';
  const STRATEGY_LAST = 'LAST';
  const STRATEGY_NEAR = 'NEAR';

  /**
   * get a comparison strategy
   *
   * @param string $strategy_name
   * @param DateTime $existing_date_time existing date in the target REDcap temporal field
   * @return AdjudicationStrategy
   */
  public static function make($strategy_name, $existing_date_time)
  {
    switch ($strategy_name) {
      case self::STRATEGY_MIN:
        return new Lowest();
        break;
      case self::STRATEGY_MAX:
        return new Highest();
        break;
      case self::STRATEGY_FIRST:
        return new Earliest();
        break;
      case self::STRATEGY_LAST:
        return new Latest();
        break;
      case self::STRATEGY_NEAR:
        return new Nearest($existing_date_time);
        break;
      default:
        return false;
        break;
    }
  }
}