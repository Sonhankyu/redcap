<?php
namespace Vanderbilt\REDCap\Classes\JsonQuery;

use JsonSerializable;
use Vanderbilt\REDCap\Classes\JsonQuery\Helpers\FilterFactory;
use Vanderbilt\REDCap\Classes\JsonQuery\Helpers\Functional;
use Vanderbilt\REDCap\Classes\JsonQuery\Helpers\Memory;
use Vanderbilt\REDCap\Classes\JsonQuery\Helpers\Node;
use Vanderbilt\REDCap\Classes\JsonQuery\Helpers\Utility;
use Vanderbilt\REDCap\Classes\JsonQuery\Interfaces\OriginatorInterface;

class JsonQuery implements OriginatorInterface, JsonSerializable
{
  /**
   * list of query related actions
   * that can be performed
   */
  const ACTION_SELECT = 'select';
  const ACTION_SELECT_STRICT = 'select strict';
  const ACTION_WHERE = 'where';
  const ACTION_OR_WHERE = 'or_where';
  const ACTION_PARENT = 'set parents';
  const ACTION_ANCESTOR = 'set ancestors';

  /**
   * original payload
   * 
   * @var array
   */
  private $payload;

  /**
   * list of nodes
   */
  private $nodes;

  /**
   * keep track of the last performed query action (function)
   *
   * @var string
   */
  private $last_action;

  /**
   * list of all performed actions
   *
   * @var array
   */
  private $actions = [];

  /**
   * memory object.
   * manages and stores snapshots
   */
  /**
   * memory manager
   *
   * @var Memory
   */
  private $memory;

  public function __construct($payload)
  {
    $this->memory = new Memory($this);
    $this->payload = $payload;
    $node = Node::from($payload);
    $this->setNodes([$node]);
    $this->memory->save();
  }

  public function reset()
  {
    $this->memory->first();
  }

  /**
   * set the state and make a snapshot
   *
   * @param Node[] $nodes
   * @param string $action one of the defined constant actions
   * @return self
   */
  public function setState($nodes, $action)
  {
    $this->setNodes($nodes);
    $this->setLastAction($action);
    $this->memory->save();
    return $this;
  }

   /**
   * set the current nodes
   *
   * @param Node[] $nodes
   * @return self
   */
  private function setNodes($nodes)
  {
    $this->nodes = $nodes;
    return $this;
  }

  /**
   * get list of nodes
   *
   * @return Node[]
   */
  public function getNodes()
  {
    return $this->nodes;
  }

  /**
   * set the last query related action.
   * this is used in the 'orWhere' to check
   * if the previous action was a 'where'
   *
   * @return string
   */
  private function setLastAction($action)
  {
    $this->last_action = $action;
    $this->actions[] = $action;
  }

  /**
   * get the latest query related action.
   * this is used in the orWhere to check
   * if the previous action was a where
   *
   * @return string
   */
  public function getLastAction()
  {
    return $this->last_action;
  }

  /**
   * Undocumented function
   *
   * @return Results
   */
  public function results($leaves_only=false)
  {
    if($leaves_only) {
      $nodes = array_filter($this->nodes, function($node) {
        return $node->isLeaf();
      });
    }else {
      $nodes = $this->nodes;
    }
    return Results::from($nodes);
  }

  /**
   * allows to use the class as a functor
   *
   * @param mixed $payload
   * @return self
   */
  public static function from($payload) {
    return new self($payload);
  }

  /**
   * check if a node path matches a query string
   *
   * @param string $query
   * @param Node $node
   * @return boolean
   */
  public function searchNodePath($query, $node)
  {
    $regexp = Filters::fixRegExp($query);
    $path_string = $node->getPathString();
    $match = preg_match($regexp, $path_string, $matches);
    return boolval($match);
  }

  /**
   * select nodes using one or more query strings.
   * The query must match the 'path' of the nodes.
   * For a more strict select version use selectStrict
   *
   * @param mixed ...$query_list a query with a path
   * @return self
   */
  public function select(...$query_list)
  {
    // partially apply a search method to get an array reducer function
    $selectReducer = function($search_method, $accumulator, $node) {
      $matches = [];
      $node->search($search_method, $matches);
      $accumulator = array_merge($accumulator, $matches);
      return array_unique($accumulator);
    };

    $current_nodes = $this->getNodes();
    $nodes = [];
    foreach ($query_list as $query) {
      $search_method = Functional::partial([$this, 'searchNodePath'], $query);
      $select_reducer = Functional::partial($selectReducer, $search_method);
      $nodes = array_reduce($current_nodes, $select_reducer, $nodes);
    }
    return $this->setState($nodes, self::ACTION_SELECT);
  }

  /**
   * collect current nodes ancestors and filter
   * them using the select queries
   *
   * @return self
   */
  public function selectAncestors(...$query_list)
  {
    $reducer = function($query, $accumulator, $node){
      $parent = $node->getParent();
      while($parent) {
        $match = $this->searchNodePath($query, $parent);
        if($match && !in_array($parent, $accumulator, $strict=true)) {
          $accumulator[] = $parent;
        }
        $parent = $parent->getParent();
      }
      return $accumulator;
    };
    $current_nodes = $this->getNodes(); // get current nodes before resetting the memor()y
    $nodes = [];
    foreach ($query_list as $query) {
      $partial_reducer = Functional::partial($reducer, $query);
      $nodes = array_reduce($current_nodes, $partial_reducer, []);
    }
    return $this->setState($nodes, self::ACTION_ANCESTOR);
  }




  /**
   * select nodes using the provided query .
   * The query must match the node name (in select must match the path)
   * of the direct children of the current node.
   * For example:
   * - a query like 'gender' will select only children called 'gender' in the current node
   * - a query like 'address/home' will select only direct descendants of the current node;
   *   firts the children with name 'address' will be selected, then, among those children,
   *   children with name 'home' will be selected
   * 
   *
   * @param mixed ...$query_list a query with a path
   * @return void
   */
  public function selectStrict(...$query_list)
  {
    $selectReducer = function($query, $accumulator, $node) {
      $selection = $node->select($query);
      return array_merge($accumulator, $selection);
    };

    $current_nodes = $this->getNodes();
    $nodes = [];
    foreach ($query_list as $query) {
      $select_reducer = Functional::partial($selectReducer, $query);
      $nodes = array_reduce($current_nodes, $select_reducer, []);
    }
    return $this->setState($nodes, self::ACTION_SELECT_STRICT);
  }

  /**
   * filter the current nodes matching the criteria
   * @see Filters class for their descriptions
   * 
   * @param string $key
   * @param string $condition
   * @param string $value
   * @return Node[]
   */
  private function single_criteria_where($key, $condition, $value)
  {
    $check_value_callback = FilterFactory::make($condition, $value);
    $check_key_callback = Functional::partialRight([Filters::class, 'checkKey'], $key);
    
    // set the search callback
    $search_callback = function($node) use($check_value_callback, $check_key_callback) {
      if($check_key_callback($node)) return false; //stop if the key does not match
      return $check_value_callback($node);
    };

    $current_nodes = $this->getNodes();
    // collect nodes that match the criteria
    $matched_nodes = [];
    foreach ($current_nodes as $current_node) {
      // if($current_node->isLeaf()) continue; // do not include leaf nodes since they have no queue
      $match = $current_node->some($search_callback);
      
      // also add the current matched node (if not contained)
      if($match && !in_array($current_node, $matched_nodes, $strict=true)) {
          $matched_nodes[] = $current_node;
      }
    }

    return $matched_nodes;
  }

  /**
   * Accept multiple 'where' criteria.
   * Facade to the single_criteria_where method
   * Values are filtered using an AND logic
   * 
   * NOTE: only check the first argument
   * for multidimensionality since 'value' could be an array
   *
   * @param array ...$criteria_list [[$key, $condition, $value]]
   * @return self
   */
  public function where(...$criteria_list)
  {
    if(!Utility::is_multidimensional_array($criteria_list)) $criteria_list = [$criteria_list];
    foreach ($criteria_list as $criteria) {
      list($key, $condition, $value) = $criteria;
      $nodes = $this->single_criteria_where($key, $condition, $value);
      $this->setState($nodes, self::ACTION_WHERE);
    } 
    return $this;
  }

  /**
   * Apply criteria after a where has already been applied
   * Values filtered with OR logic
   * (or perfrom a normal where if needed)
   * 
   * NOTE: only check the first argument
   * for multidimensionality since 'value' could be an array
   * 
   * @param array ...$criteria_list
   * @return self
   */
  public function orWhere(...$criteria_list)
  {
    if(!Utility::is_multidimensional_array($criteria_list)) $criteria_list = [$criteria_list];
    $last_action = $this->getLastAction();
    // if previous action was not 'where' then perform a 'where' first;
    // this allows to use 'orWhere' as first filter
    if($last_action !== self::ACTION_WHERE) {
      $criteria = array_shift($criteria_list);
      $this->where(...$criteria);
    }

    if(count($criteria_list)<1) return; // exit if no more criteria

    // - collect current results
    // - go to previous state
    // - get results for each set of criteria
    // - merge them with the current ones
    $nodes = $this->getNodes();
    $this->memory->prev();
    foreach ($criteria_list as $criteria) {
      $new_nodes = $this->single_criteria_where(...$criteria); //creates new state
      $nodes = array_merge($nodes, $new_nodes);
    }
    $this->setState($nodes, self::ACTION_OR_WHERE);

    return $this;
  }

  /**
   * get the parent ([TODO] at specified level)
   * of current nodes
   *
   * @param integer $level
   * @return JsonQuery
   */
  public function parent($level=1)
  {
    if(!is_numeric($level) || $level<1) throw new \Exception("The parent level must be a number bigger than 0", 400);
    $nodes = [];
    $current_nodes = $this->getNodes(); // get current nodes before resetting the memor()y
    foreach ($current_nodes as $node) {
      $parent = $node->getParent();
      while(--$level>0 && $parent) {
        $parent = $parent->getParent();
      }
      if($parent && !in_array($parent, $nodes, true)) $nodes[] = $parent;
    }
    return $this->setState($nodes, self::ACTION_PARENT);
  }

  /**
   * default action to perform when the class is invoked as a method
   *
   * @return Node[]
   */
  public function __invoke()
  {
    return $this->getNodes();
  }

  public function jsonSerialize()
  {
    return $this->getNodes();
  }

  /**
   * create a snapshot with the current nodes
   *
   * @return void
   */
  function makeSnapshot()
  {
    $nodes = $this->getNodes();
    $action = $this->getLastAction();
    return new JsonQuerySnapshot($nodes, $action);
  }

  /**
   * Restores the Originator's state from a memento object.
   *
   * @param JsonQuerySnapshot $snapshot
   * @return void
   */
  public function restoreSnapshot($snapshot)
  {
    $this->setLastAction($snapshot->getAction());
    $this->setNodes($snapshot->getNodes());
  }

}