<?php
namespace Vanderbilt\REDCap\Classes\Fhir\ClinicalDataPull\AutoAdjudication;

use DateTime;

/**
 * class used to compare temporal data
 */
class ComparisonElement
{

  /**
   *
   * @var mixed
   */
  private $value;

  /**
   *
   * @var DateTime
   */
  private $date_time;

  /**
   *
   * @var int
   */
  private $record_data_id;

  /**
   *
   * @param mixed $value
   * @param DateTime $date_time
   * @return void
   */
  public function __construct($value, $date_time, $record_data_id=null)
  {
    $this->value = $value;
    $this->record_data_id = $record_data_id;
    $this->date_time = $date_time;
  }

  /**
   * Undocumented function
   *
   * @return mixed
   */
  public function getValue()
  {
    return $this->value;
  }

  /**
   * Undocumented function
   *
   * @return int|null
   */
  public function getID()
  {
    return $this->record_data_id;
  }

  /**
   * Undocumented function
   *
   * @return DateTime
   */
  public function getDateTime()
  {
    return $this->date_time;
  }

}