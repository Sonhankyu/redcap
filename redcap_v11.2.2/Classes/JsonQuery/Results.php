<?php
namespace Vanderbilt\REDCap\Classes\JsonQuery;

use Vanderbilt\REDCap\Classes\JsonQuery\Helpers\Node;

class Results
{
  /**
   * list of nodes
   *
   * @var Node[]
   */
  private $nodes;

  /**
   * Undocumented function
   *
   * @param Node[] $nodes
   */
  public function __construct($nodes)
  {
    $this->nodes = $nodes;
  }

  /**
   * get nodes
   *
   * @return Node[]
   */
  private function nodes()
  {
    return $this->nodes;
  }

   /**
   * return one of the values
   *
   * @param integer $index
   * @return mixed
   */
  public function single($position=0)
  {
    $results = $this->all();
    return @$results[$position] ?: '';
  }

  /**
   * return all values as array
   * (only leaf ndoes)
   *
   * @return array
   */
  public function all()
  {
    $nodes = $this->nodes();
    $results = [];
    foreach ($nodes as $key => $node) {
      if($node->isLeaf()) $results[] = $node->getValue();
    }
    return $results;
  }

  /**
   * return all values string joined by a separator
   *
   * @return string
   */
  public function join($separator=' ')
  {
    return implode($separator, $this->all());
  }

  /**
   * return values in results grouped by parent.
   * @see expand to return the whole structure
   *
   * @return array|object
   */
  public function group($index=null)
  {
    /**
     * groups nodes with their parents
     */
    $groupNodes = function($accumulator, $node) {
      $parent = $node->getParent();
      $key = $node->getName();
      if($node->isLeaf()) {
        $accumulator[$parent->getPathString()][$key] = $node->getValue();
      }
      return $accumulator;
    };
    $nodes = $this->nodes();
    /* $non_parent_nodes = Node::exclude_parents($nodes); // exclude parents of the nodes in the results
    $groups = array_reduce($non_parent_nodes, $groupNodes, []); */
    
    $groups = array_reduce($nodes, $groupNodes, []);
    $indexed_groups = array_values($groups);
    if(is_numeric($index)) return @$indexed_groups[$index];
    return $indexed_groups ?: [];
  }

  /**
   * expand all nodes.
   * Similar to 'group' but all nodes are displayed
   * including those that have children in the list.
   *
   * @param int $index
   * @return array|object
   */
  function expand($index=null)
  {
    $nodes = $this->nodes();
    $expanded = array_map(function($node) {return $node->expand();}, $nodes);
    if(is_numeric($index)) return @$expanded[$index];
    return $expanded;
  }
  

  /**
   * FUNCTORS
   */

  /**
  * functor constructor
  *
  * @param Node[] $nodes
  * @return self
  */
  public static function from($nodes)
  {
    return new static($nodes);
  }

  /**
   * functor that applis a filter to the nodes
   *
   * @param callable $function
   * @return self
   */
  public function filter($function)
  {
    $filtered = array_filter($this->all(), $function, ARRAY_FILTER_USE_BOTH);
    return static::from($filtered);
  }

  /**
   * functorthat applies a function to all nodes
   *
   * @param callable $function
   * @return self
   */
  public function map($function)
  {
    if(!is_callable($function)) throw new \Exception("Invalid callable", 400);
    $nodes = [];
    foreach ($this->nodes as $branch) {
      $branchValue = $branch->getValue();
      $new_value = $function($branchValue);
      $branch->setValue($new_value);
      $nodes[] = $branch;
    }
    return static::from($nodes);
  }

  /**
   * functor to reduce nodes to a sigle node
   *
   * @param callable $function
   * @param mixed $seed
   * @return self
   */
  public function reduce($function, $initial)
  {
    if(!is_callable($function)) throw new \Exception("Invalid callable", 400);
    $reduced = array_reduce($this->all(), $function, $initial);
    return static::from($reduced);
  }

  /**
   * return all values if invoked
   *
   * @return array
   */
  public function __invoke()
  {
    return $this->all();
  }
}