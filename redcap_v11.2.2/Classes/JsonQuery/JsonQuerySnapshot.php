<?php
namespace Vanderbilt\REDCap\Classes\JsonQuery;

use DateTime;
use Vanderbilt\REDCap\Classes\JsonQuery\Interfaces\SnapshotInterface;

class JsonQuerySnapshot implements SnapshotInterface
{
  private $nodes;
  private $date;
  private $action;
  private $id;

  /**
   * Undocumented function
   *
   * @param Node[] $nodes
   * @param string $action
   */
  public function __construct($nodes, $action)
  {
    $this->id = uniqid ($prefix="__" , $more_entropy=false);
    $this->date = new DateTime();
    $this->nodes = $nodes;
    $this->action = $action;
  }

  /**
   *
   * @return Node[]
   */
  public function getNodes()
  {
    return $this->nodes;
  }

  /**
   * get the name of the function
   * used when saving the snapshot
   *
   * @return string
   */
  public function getAction()
  {
    return $this->action;
  }

  /**
   * interface method
   *
   * @return DateTime
   */
  public function getDate()
  {
    return $this->date;
  }

  public function getID()
  {
    return $this->id;
  }

  public function getDescription()
  {
    $id = $this->getID();
    $date = $this->getDate();
    $total_nodes = count($this->getNodes());
    $action = $this->getAction();
    return sprintf("ID %s (%s) - %s - Nodes: %u", $id, $date->format('Y-m-d H:i:s'), $action, $total_nodes);
  }

}