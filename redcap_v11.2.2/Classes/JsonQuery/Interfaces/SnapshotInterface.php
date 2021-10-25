<?php
namespace Vanderbilt\REDCap\Classes\JsonQuery\Interfaces;

use DateTime;

interface SnapshotInterface
{
  /**
   *
   * @return DateTime
   */
  public function getDate();
  /**
   *
   * @return mixed
   */
  public function getID();

  /**
   *
   * @return string
   */
  public function getDescription();
}