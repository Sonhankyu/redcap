<?php
namespace Vanderbilt\REDCap\Classes\JsonQuery\Helpers;

use Vanderbilt\REDCap\Classes\JsonQuery\Filters;

/**
 * factory for the value filters of a Node
 */
class FilterFactory
{
  public static function make($condition, $value)
  {
    $condition = strtolower($condition);
    switch($condition) {
      case '=':
        $callback = Functional::partialRight([Filters::class, 'isEqual'], $value);
        break;
      case '!=':
        $callback = Functional::partialRight([Filters::class, 'isNotEqual'], $value);
        break;
      case '>':
        $callback = Functional::partialRight([Filters::class, 'isBigger'], $value);
        break;
      case '>=':
        $callback = Functional::partialRight([Filters::class, 'isBiggerOrEqual'], $value);
        break;
      case '<':
        $callback = Functional::partialRight([Filters::class, 'isSmaller'], $value);
        break;
      case '<=':
        $callback = Functional::partialRight([Filters::class, 'isSmallerOrEqual'], $value);
        break;
      case 'in':
        $callback = Functional::partialRight([Filters::class, 'isIn'], $value);
        break;
      case 'not in':
        $callback = Functional::partialRight([Filters::class, 'isNotIn'], $value);
        break;
      case 'beetween':
        $callback = Functional::partialRight([Filters::class, 'isBeetween'], $value);
        break;
      case 'not beetween':
        $callback = Functional::partialRight([Filters::class, 'isNotBeetween'], $value);
        break;
      case 'not like':
        $callback = Functional::partialRight([Filters::class, 'isNotLike'], $value);
        break;
      case 'like':
        $callback = Functional::partialRight([Filters::class, 'isLike'], $value);
        break;
    }
    return $callback;
  }
 }