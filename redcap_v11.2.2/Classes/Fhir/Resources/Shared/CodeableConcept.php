<?php
namespace Vanderbilt\REDCap\Classes\Fhir\Resources\Shared;

use Vanderbilt\REDCap\Classes\Fhir\Resources\AbstractResource;

/**
 * A codeable concept is a reference to a terminology.
 * Contains
 * - a "coding" element (array)
 * - a "text" element (string)
 * 
 * @see https://www.hl7.org/fhir/datatypes.html#CodeableConcept
 */
class CodeableConcept extends AbstractResource
{
  /**
   * prefix of the properties
   *
   * @var string
   */
  private $prefix;
  /**
   * name to use for creating a prefix.
   * this is usually the same name
   * of the propery in the parent object (i.g. code for Observation)
   *
   * @var string
   */
  private $name;

  /**
   * create a codeable concept
   *
   * @param string $name
   * @param array $payload
   */
  public function __construct($name, $payload)
  {
    $this->name = $name;
    $this->prefix = $name.'-';
    parent::__construct($payload);
  }

  public function getCoding()
  {
    return $this->query()
      ->select('#/\d+/coding/\d+$')
      ->results()
      ->expand();
  }

  public function getText()
  {
    return $this->query()
      ->select('#/\d+/text$')
      ->results()
      ->single();
  }

  public function getData()
  {
    $data = [
      'text'  => $this->getText(),
      'coding'=> $this->getCoding(),
    ];
    return $data;
  }
  
}