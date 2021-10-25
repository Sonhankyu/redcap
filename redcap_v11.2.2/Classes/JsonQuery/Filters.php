<?php
namespace Vanderbilt\REDCap\Classes\JsonQuery;

use Vanderbilt\REDCap\Classes\JsonQuery\Helpers\Node;

/**
 * Filters for Node elements.
 * 
 * Note: since Node::search needs a Node as only argument
 * for the search callback, all this functions can be used
 * applying first the $value with a Functional::partialRight
 */
class Filters
{
  /**
   * Check if node value is equal
   *
   * @param Node $node
   * @param mixed $value
   * @return boolean
   */
  public static function isEqual($node, $value)
  {
    return $node->getValue()==$value;
  }
    
  /**
   * Check if node value is not equal
   *
   * @param Node $node
   * @param mixed $value
   * @return boolean
   */
  public static function isNotEqual($node, $value)
  {
    return $node->getValue()!=$value;
  }
    
  /**
   * Check if node value is bigger
   *
   * @param Node $node
   * @param mixed $value
   * @return boolean
   */
  public static function isBigger($node, $value)
  {
    return $node->getValue()>$value;
  }
    
  /**
   * Check if node value is bigger or equal
   *
   * @param Node $node
   * @param mixed $value
   * @return boolean
   */
  public static function isBiggerOrEqual($node, $value)
  {
    return $node->getValue()>=$value;
  }
    
  /**
   * Check if node value is smaller
   *
   * @param Node $node
   * @param mixed $value
   * @return boolean
   */
  public static function isSmaller($node, $value)
  {
    return $node->getValue()<$value;
  }
    
  /**
   * Check if node value is smaller or equal
   *
   * @param Node $node
   * @param mixed $value
   * @return boolean
   */
  public static function isSmallerOrEqual($node, $value)
  {
    return $node->getValue()<=$value;
  }
    
  /**
   * Check if node value matches a regular expression
   *
   * @param Node $node
   * @param mixed $value
   * @return boolean
   */
  public static function isLike($node, $value) {
    $node_value = $node->getValue();
    $regexp = static::fixRegExp($value, Node::PATH_SEPARATOR);
    $match = preg_match($regexp, $node_value);
    return $match;
  }
  
  /**
   * Check if node value DOES NOT matches a regular expression
   *
   * @param Node $node
   * @param mixed $value
   * @return boolean
   */
  public static function isNotLike($node, $value)
  {
    return !self::isLike($node, $value);
  }
  
  /**
   * Check if node value is included in a list of values
   *
   * @param Node $node
   * @param string $key
   * @param array $value list of values
   * @return boolean
   */
  public static function isIn($node, $value)
  {
    if(!is_array($value)) throw new \Exception("'in' needs a list of values", 400);
    $node_value = $node->getValue();
    $current_value = current($value);
    $match = false;
    while($current_value && !$match) {
      $match = $node_value===$current_value;
      $current_value = next($value);
    }
    return $match;

  }
  
  /**
   * Check if node value is NOT included in a list of values
   *
   * @param Node $node
   * @param string $key
   * @param array $value
   * @return boolean
   */
  public static function isNotIn($node, $value)
  {
    return !self::isIn($node, $value);
  }
  
  /**
   * Check if node value is beetween 2 values (start and end included)
   *
   * @param Node $node
   * @param string $key
   * @param array $value
   * @return boolean
   */
  public static function isBeetween($node, $value)
  {
    if(!is_array($value) || count($value)!=2) throw new \Exception("'beetween' needs 2 values", 400);
    list($start, $end) = $value;
    $node_value = $node->getValue();
    return $node_value>=$start && $node_value<=$end;
  }
  
  /**
   * Check if node value is NOT beetween 2 values (start and end included)
   *
   * @param Node $node
   * @param string $key
   * @param array $value
   * @return boolean
   */
  public static function isNotBeetween($node, $value)
  {
    return !self::isBeetween($node, $value);
  }

  /**
   * Check the key of a node
   * 
   * PLEASE NOTE: this is the only function checking the key
   *
   * @param Node $node
   * @param string $key
   * @return boolean
   */
  public static function checkKey($node, $key)
  {
    return $node->getName() !== $key;
  }

  /**
   * make sure to get a valid regular expression:
   * - keep whole structure with flags if delimiters are detected
   *
   * @param string $string
   * @return string
   */
  public static function fixRegExp($string)
  {
    if(preg_match("/^\/(?<content>.*)\/(?<flags>[\w]*)$/", $string, $matches)) {
      $escaped = addcslashes(@$matches['content'], Node::PATH_SEPARATOR);
      $regexp = sprintf("/%s/%s", $escaped, @$matches['flags']);
    }else {
      $escaped = addcslashes($string, Node::PATH_SEPARATOR);
      $regexp = sprintf("/%s/", $escaped);
    }
    return $regexp;
  }
    
}