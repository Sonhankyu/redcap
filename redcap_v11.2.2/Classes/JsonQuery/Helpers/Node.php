<?php
namespace Vanderbilt\REDCap\Classes\JsonQuery\Helpers;

use Iterator;
use JsonSerializable;

class Node implements Iterator, JsonSerializable
{
  const PATH_SEPARATOR = '/';
  const ROOT_NAME = '#';
  private $parent = null;
  private $children = [];
  private $name;
  private $value;
  private $queue = [];

  /**
   *
   * @param string $name
   * @param Node $parent
   */
  public function __construct($name=null, $parent=null)
  {
    $this->name = $name;
    $this->parent = $parent;
  }

  /**
   * create a Node from a payload
   *
   * @param array|object $payload
   * @return Node
   */
  public static function from($payload)
  {
    $root = static::breadthFirst($payload);
    return $root;
  }

   /**
   * traverse a JSON payload and create
   * the node structure
   *
   * @param array|object $payload
   * @param array $path
   * @param Node $parent_node
   * @return Node
   */
  private static function breadthFirst($payload, $parent_node=null)
  {
    if(!$parent_node) $parent_node = new self(self::ROOT_NAME);

    foreach ($payload as $key => $value) {
      $node = new self($key, $parent_node);
      $parent_node->addNode($node);

      if(is_object($value) || is_array($value)) {
        static::breadthFirst($value, $node);
      }else {
        $node->setValue($value);
      }

    }
    return $parent_node;
  }

  /**
   * cached version of the descendants
   *
   * @return Node[]
   */
  public function getQueue()
  {
    if($this->queue) return $this->queue;
    return $this->queue = $this->getDescendants();
  }

  /**
   * get the current node in the queue
   *
   * @return Node|false
   */
  public function current()
  {
    return current($this->getQueue());
  }

  /**
   * move the cursor to the precedent position
   *
   * @return Node|false
   */
  public function next()
  {
    return next($this->getQueue());
  }
  /**
   * move the cursor to the precedent position
   *
   * @return Node|false
   */
  public function prev()
  {
    return prev($this->getQueue());
  }
  /**
   * bring the cursor to its initial position
   *
   * @return Node
   */
  public function rewind()
  {
    return rewind($this->getQueue());
  }
  /**
   * return the key of the cursor
   * in the queue list
   * 
   *
   * @return int|string|null
   */
  public function key()
  {
    return key($this->getQueue());
  }

  /**
   * check if a Node is valid
   *
   * @return boolean
   */
  public function valid()
  {
    $key = $this->key();
    return isset($this->getQueue()[$key]);
  }
  /**
   * move the cursor and return the last element in the queue
   *
   * @return Node
   */
  public function last()
  {
    return end($this->getQueue());
  }

  /**
   * check if the cnode is contained
   * by a specified node
   *
   * @param Node $node
   * @return boolean
   */
  public function isContainedBy($node)
  {
    $queue = $node->getDescendants();
    return in_array($this, $queue, true);
  }

  /**
   * check if the node contain a node
   *
   * @param Node $node
   * @return boolean
   */
  public function contains($node)
  {
    $queue = $this->getDescendants();
    return in_array($node, $queue, true);
  }

  /**
   * get the parent node
   *
   * @return Node
   */
  public function getParent()
  {
    return $this->parent;
  }

  /**
   * check if the node is a leaf
   *
   * @return boolean
   */
  public function isLeaf()
  {
    return count($this->getChildren())===0;
  }

  public function isRoot()
  {
    return is_null($this->getParent());
  }

  /**
   * get the value of the node
   *
   * @return void
   */
  public function getValue()
  {
    return $this->value;
  }

  /**
   * get the path of the node as a string
   *
   * @return string
   */
  public function getPathString()
  {
    return implode(self::PATH_SEPARATOR, $this->getPath());
  }

  /**
   * get the path of the current node
   *
   * @return array
   */
  public function getPath()
  {
    $name = $this->getName();
    $parent = $this->getParent();
    if(!$parent) return [$name];
    $path = $parent->getPath();
    $path[] = $name;
    return $path;
  }

  /**
   * get the name of the current node
   *
   * @return string|int
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Iterates over each node in the hierarchy
   * passing them to the callback function.
   * If the callback function returns true,
   * the current value from array is returned
   * and the search stops. 
   *
   * @param  $callback
   * @param boolean $found
   * @return boolean return false if nothing found
   */
  function search($callback, &$matches) {
    $matches = is_array($matches) ? $matches : [];
    $result = $callback($this);
    if($result==true) {
      $matches[] = $this;
    }
    $children = $this->getChildren();
    foreach ($children as $child) {
      $child_result = $child->search($callback, $matches);
      $result = $result || $child_result;
    }

    return $result;
  }

  /**
   * Iterates over each node in the hierarchy
   * passing them to the callback function.
   * If the callback function returns true,
   * the current value from array is returned
   * and the search stops. 
   *
   * @param  $callback
   * @param boolean $found
   * @return Node|false return false if nothing found
   */
  function some($callback, &$found=false) {
    if($found) return true;
    $found = $callback($this);
    if($found==true) return true;
    $children = $this->getChildren();
    foreach ($children as $child) {
      $child->some($callback, $found);
      if($found==true) return true; // exit as the node is found
    }

    return false;
  }

  /**
   * extract the json object
   * that reflects the object hierarchy
   *
   * @return array|object
   */
  function expand($json=[]) {
    $nested = &$json;
    if($this->isLeaf()) {
      return $this->getValue();
    }else {
      $nested = $nested[(string)$this->getName()];
      $children = $this->getChildren();
      foreach ($children as $child) {
        $value = $child->expand();
        $nested[$child->getName()] = $value;
      }
    }
    return $json;
  }

  /**
   * add a children to the current node
   *
   * @param Node $node
   * @return void
   */
  public function addNode($node)
  {
    $this->children[] = $node;
  }

  /**
   * get a list of children
   *
   * @return Node[]
   */
  public function getChildren()
  {
    return $this->children;
  }


  /**
   * recursively get all ancestors
   *
   * @return Node[]
   */
  public function getAncestors()
  {
    $parent = $this->getParent();
    if(!$parent) return [];
    $ancestors = $parent->getAncestors();
    $ancestors[] = $parent;
    return $ancestors;
  }

  /**
   * recursively get all parents
   *
   * @return Node[]
   */
  public function getDescendants()
  {
    $descendants = [];
    $children = $this->getChildren();
    foreach($children as $child) {
      $descendants[] = $child;
      $child_descendants = $child->getDescendants();
      $descendants = array_merge($descendants, $child_descendants);
    }
    return $descendants;
  }

  /**
   * Get a linear representation of all branches.
   * the final element is the value of the leaf.
   *
   * @return array[]
   */
  public function getBranches()
  {
    $name = $this->getName();
    $children = $this->getChildren();
    if($this->isLeaf()) return [[$name, $this->getValue()]];
    $branches = [];
    foreach ($children as $child) {
      $sub_branches = $child->getBranches();
      foreach($sub_branches as $sub_branch) {
        array_unshift($sub_branch, $name);
        $branches[] = $sub_branch;
      }
    }
    return $branches;
  }

  public function select($path)
  {
    $paths = explode(Node::PATH_SEPARATOR, $path);
    $sliced_path = array_splice($paths, 0, 1);
    $first_path = reset($sliced_path);
    $updated_path = implode(Node::PATH_SEPARATOR, $paths);
    $sub_select = (count($paths)>0);

    $selection = [];
    $children = $this->getChildren();
    $regexp = sprintf("/^%s$/", addcslashes($first_path, Node::PATH_SEPARATOR));
    foreach ($children as $child) {
      $match = preg_match($regexp, $child->getName());
      if($match) {
        if($sub_select) {
          $child_selection = $child->select($updated_path);
          $selection = array_merge($selection, $child_selection);
        }else {
          $selection[] = $child;
        }
      }
    }
    return $selection;
  }

  /**
   * set a value in a node
   *
   * @param mixed $value
   * @return void
   */
  public function setValue($value)
  {
    $this->value = $value;
  }

  /**
   * get a string representation of the node
   *
   * @return string
   */
  public function __toString()
  {
    $full_path = $this->getPath();
    $full_path[] = $this->value;
    return implode(self::PATH_SEPARATOR, $full_path);
  }

  /**
   * process a list to exclude nodes that have children
   * in the same list
   *
   * @param Node[] $nodes
   * @return Node[]
   */
  public static function exclude_parents($nodes)
  {
    /**
     * compare a node with a list of nodes
     */
    $filter = function($node) use($nodes) {
      $is_parent = false;
      $current_node = current($nodes);
      while($current_node && !$is_parent) {
        $is_parent = $node->contains($current_node);
        $current_node = next($nodes);
      }
      return !$is_parent;
    };

    $only_children = array_filter($nodes, $filter);
    $only_children = array_values($only_children); //reset keys
    return $only_children;
  }

  /**
   * build the json object
   * that reflects the current hierarchy
   *
   * @return array|object
   */
  public function expand_ALT()
  {
    $unzip = function($branch, $json=[]) use(&$unzip){
      $nested = &$json;
      if($branch->isLeaf()) {
        return $branch->getValue();
      }else {
        $nested = $nested[(string)$branch->getName()];
        $children = $branch->getChildren();
        foreach ($children as $child) {
          $value = $unzip($child, []);
          $nested[$child->getName()] = $value;
        }
      }
      return $json;
    };
    $expanded = $unzip($this);
    return $expanded;
  }



  public function jsonSerialize()
  {
    $metadata = [
      'name' => $this->name,
      'leaf' => $this->isLeaf(),
      'path' => $this->getPathString(),
      'value' => $this->value,
      'queue size' => count($this->queue),
      'total children' => count($this->children),
    ];
    $data = $this->expand();
    return compact('metadata', 'data');
  }

}