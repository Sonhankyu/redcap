<?php namespace Vanderbilt\REDCap\Classes\JsonQuery\Helpers;


class Utility
{

  public static function is_multidimensional_array($array) {
    foreach ($array as $value) {
      if (is_array($value)) return true;
    }
    return false;
  }
  
  /**
   * create a flat version of the payload.
   * each row is createad using a (flatten strategy) function
   *
   * @param mixed $object
   * @param array $flattened
   * @param array $path
   * @param string $strategy callable that accepts 2 parameters: $path, $value (TODO)
   * @return array
   */
  public static function flatten($object, $strategy, $flattened=[], $path=[])
  {
    foreach($object as $key => $value)
    {
      $path[] = $key;
      if(is_array($value) || is_object($value))
      {
        $flattened = static::flatten($value, $strategy, $flattened, $path);
        array_pop($path);
        continue;
      }
      $flattened[] = $strategy($path, $value);
      array_pop($path);
    }
    return $flattened;
  }
}