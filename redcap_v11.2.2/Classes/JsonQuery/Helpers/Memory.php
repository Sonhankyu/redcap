<?php
namespace Vanderbilt\REDCap\Classes\JsonQuery\Helpers;

use Vanderbilt\REDCap\Classes\JsonQuery\Interfaces\SnapshotInterface;

class Memory
{
  /**
   *
   * @var SnapshotInterface[]
   */
  private $snapshots = [];
  private $originator;

  /**
   *
   * @param OriginatorInterface $originator
   */
  public function __construct($originator)
  {
    $this->originator = $originator;
  }

  public function save(): void
  {
    $this->snapshots[] = $this->originator->makeSnapshot();
    end($this->snapshots);
  }

  public function prev(): void
  {
    $snapshot = prev($this->snapshots);
    if(!$snapshot) return;
    $this->originator->restoreSnapshot($snapshot);
  }

  public function first()
  {
    $snapshot = reset($this->snapshots);
    $this->originator->restoreSnapshot($snapshot);
  }

  public function last()
  {
    $snapshot = end($this->snapshots);
    if(!$snapshot) return;
    $this->originator->restoreSnapshot($snapshot);
  }

  public function goToSnapshot($position=null)
  {
    if(!isnumber($position)) return;
    while (key($this->snapshots) !== $position) $snapshot = next($this->snapshots);
    if(!$snapshot) return;
    $this->originator->restoreSnapshot($snapshot);
  }

  public function count()
  {
    return count($this->snapshots);
  }

  public function getHistory(): array
  {
    $history = array_map(function(SnapshotInterface $snapshot) {
      return $snapshot->getDescription();
    }, $this->snapshots);
    return $history;
  }
}