<?php
namespace Vanderbilt\REDCap\Classes\JsonQuery\Interfaces;

interface OriginatorInterface
{
  public function makeSnapshot();
  /**
   * Undocumented function
   *
   * @param Snapshot $snapshot
   * @return void
   */
  public function restoreSnapshot($snapshot);
}