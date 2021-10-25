<?php
namespace Vanderbilt\REDCap\Classes\Fhir\Resources\Shared;

use Vanderbilt\REDCap\Classes\Fhir\Resources\AbstractResource;
use Vanderbilt\REDCap\Classes\Fhir\Resources\Traits\CanNormalizeTimestamp;

class Observation extends AbstractResource
{

  use CanNormalizeTimestamp;

  const TIMESTAMP_FORMAT = 'Y-m-d H:i';

  /**
   * get the local or GMT timestamp
   * 
   * @param boolean $localTimestamp
   * @return string
   */
  public function getTimestamp($localTimestamp=false)
  {
    $callable = $this->getTimestampCallable($this->getDate(), self::TIMESTAMP_FORMAT);
    return $callable($localTimestamp);
  }

  public function getId()
  {
    return $this->query()
      ->select('#/id')
      ->results()
      ->single();
  }

  /**
   * return the category of the observation (Laboratory, Lab, Vital Signs)
   *
   * @return void
   */
  public function getCategory()
  {
    return $this->query()
      ->select('#/category/\d+/text')
      ->results()
      ->all();
  }

  /**
   * select a loinc code
   * valid selections are:
   * - '#/code/coding/\d+': \d+, any number, means that we are selecting the list of coding
   * - '#/code/coding/.*': other version the dot (.) means any character
   * @return array
   */
  public function getLoincCodes()
  {
    return $this->query()
      // ->select('#/code/coding/\d+') //
      ->select('#/code/coding/.*') //
      ->where('system', 'like', CodingSystem::LOINC)
      ->select('code$')
      ->results()
      ->all();
  }

  public function getLoincDisplays()
  {
    return $this->query()
      ->select('#/code/coding/\d+')
      ->where('system', 'like', CodingSystem::LOINC)
      ->select('display$')
      ->results()
      ->all();
  }

  public function getDate()
  {
    return $this->query()
      ->select('#/effectiveDateTime')
      ->results()
      ->single();
  }

  public function getValueQuantity()
  {
    return $this->query()
      ->select('#/valueQuantity')
      ->results()
      ->group(0);
  }

  public function getValueString()
  { 
    return $this->query()
    ->select('#/valueString')
    ->results()
    ->single();
  }
  public function getValueCodeableConcept()
  {
    return $this->query()
    ->select('#/valueCodeableConcept')
    ->results()
    ->group();
  }
  public function getValueBoolean()
  {
    return $this->query()
    ->select('#/valueBoolean')
    ->results()
    ->single();
  }
  public function getValueInteger()
  {
    return $this->query()
    ->select('#/valueInteger')
    ->results()
    ->single();
  }
  public function getValueRange()
  {
    return $this->query()
    ->select('#/valueRange')
    ->results()
    ->single();
  }
  public function getValueRatio()
  {
    return $this->query()
    ->select('#/valueRatio')
    ->results()
    ->group();
  }
  public function getValueSampledData()
  {
    return $this->query()
    ->select('#/valueSampledData')
    ->results()
    ->group();
  }
  public function getValueTime()
  {
    return $this->query()
    ->select('#/valueTime')
    ->results()
    ->single();
  }
  public function getValueDateTime()
  {
    return $this->query()
    ->select('#/valueDateTime')
    ->results()
    ->single();
  }
  public function getValuePeriod()
  {
    return $this->query()
    ->select('#/valuePeriod')
    ->results()
    ->group();
  }

  public function getValue()
  {
    return $this->query()
      ->select(
        '#/valueQuantity/value','#/valueString','#/valueBoolean',
        '#/valueInteger', '#/valueTime', '#/valueDateTime'
      )->results()
      ->single();
  }

  public function getValueUnit()
  {
    return $this->query()
      ->select('#/valueQuantity/unit')
      ->results()
      ->single();
  }

  /**
   * components are subset that need to be split.
   * each component only contains
   * - 1 `code` with 1 or more coding systems
   * - 0 or 1 `value`
   * @see https://www.hl7.org/fhir/observation.html
   *
   * @return array
   */
  public function getComponent()
  {
    return $this->query()
      ->select('#/component/\d+$')
      ->results()
      ->expand();
  }

  
  public function getCodingSystems()
  {
    return $this->query()
      ->select('#/code/coding/\d+$')
      ->results()
      ->expand();
  }

  /**
   * create a CodeableConcept from the code
   * portion of the payload
   *
   * @return CodeableConcept
   */
  public function getCode()
  {
    $payload = $this->query()
      ->select('#/code$')
      ->results()
      ->expand();
    return new CodeableConcept('code', $payload);
  }

  /**
   * create separate observations
   * for each component
   *
   * @return Observation[]
   */
  public function splitComponents()
  {
    $components = $this->getComponent();
    if(empty($components)) return [$this];
    $parentPayload = $this->getPayload();
    unset($parentPayload['component']);
    $list = array_map(function($payload) use($parentPayload) {
      return $this->replacePayload($parentPayload, $payload);
    }, $components);
    return $list;
  }

  /**
   * create separate observations
   * for each coding
   *
   * @return Observation[]
   */
  public function splitCodings()
  {
    $codeableConcept = $this->getCode();
    $codings = $codeableConcept->getCoding();
    if(count($codings)<=1) return [$this];
    $text = $codeableConcept->getText();
    $parentPayload = $this->getPayload();
    $list = array_map(function($coding) use($parentPayload, $text) {
      // make a new code payload that will replace the existing one
      $payload = [
        'code' => [
          'coding' => [$coding],
          'text' => $text,
        ]
      ];
      return $this->replacePayload($parentPayload, $payload);
    }, $codings);
    return $list;
  }

  /**
   * observation resources should always be returned
   * as array because could contain components.
   * 
   * splits first based on components, then based on codings
   * @see https://www.hl7.org/fhir/observation-definitions.html#Observation.component
   *
   * @return Observation[]
   */
  public function split()
  {
    $reduceObservations = function($carry, $observation) {
      $list = $observation->splitCodings();
      foreach ($list as $splitted) {
          $carry[] = $splitted;
      }
      return $carry;
    };
    $observations = $this->splitComponents();
    $list = array_reduce($observations, $reduceObservations, []);
    return $list;
  }

  public function getNormalizedTimestamp()
  {
    $timestamp = $this->getDate();
    return $this->getGmtTimestamp($timestamp, self::TIMESTAMP_FORMAT);
  }

  /**
   * get plain version of the data
   * 
   * @return array
   */
  public function getData()
  {
    $data = [
      'fhir-id' => $this->getId(),
      'code' => $this->getCode()->getData(),
      'category' => $this->getCategory(),
      'timestamp' => $this->getDate(),
      'normalized_timestamp' => $this->getNormalizedTimestamp(),
      'value' => $this->getValue(),
      'valueUnit' => $this->getValueUnit(),
      'valueQuantity' => $this->getValueQuantity(),
      'valueString' => $this->getValueString(),
      'valueCodeableConcept' => $this->getValueCodeableConcept(),
      'valueBoolean' => $this->getValueBoolean(),
      'valueInteger' => $this->getValueInteger(),
      'valueRange' => $this->getValueRange(),
      'valueRatio' => $this->getValueRatio(),
      'valueSampledData' => $this->getValueSampledData(),
      'valueTime' => $this->getValueTime(),
      'valueDateTime' => $this->getValueDateTime(),
      'valuePeriod' => $this->getValuePeriod(),
      'component' => $this->getComponent(),
    ];
    return $data;
  }
  
}