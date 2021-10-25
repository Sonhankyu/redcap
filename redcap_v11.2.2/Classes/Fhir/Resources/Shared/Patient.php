<?php
namespace Vanderbilt\REDCap\Classes\Fhir\Resources\Shared;

use Vanderbilt\REDCap\Classes\Fhir\Resources\AbstractResource;

class Patient extends AbstractResource
{

  public function getFhirID()
  {
    return $this->query()->select('#/id$')->results()->single();
  }

  public function getIdentifier($system='')
  {
    return $this->query()
      ->select('#/identifier/\d+')
      ->where('system', '=', $system)
      ->select('value')
      ->results()
      ->single();
  }

  public function getIdentifiers()
  {
    return $this->query()
      ->select('#/identifier/\d+$')
      ->results()
      ->expand();
  }
  
  public function getNameGiven($index=0)
  {
    return $this->query()
      ->select('#/name/\d+')
      ->where('use', '=', 'official')
      ->select('given')
      ->results()
      ->join(' ');
  }
  
  public function getNameFamily($index=0)
  {
    return $this->query()
      ->select('#/name/\d+')
      ->where('use', '=', 'official')
      ->select('family')
      ->results()
      ->join(' ');
  }
  
  public function getBirthDate()
  {
    return $this->query()
      ->select('#/birthDate')
      ->results()
      ->single();
  }
  
  public function getGenderCode()
  {
    return $this->query()
      ->select('#/extension/\d+')
      ->where('url', 'like', 'birth-?sex$')
      ->select('code')
      ->results()
      ->single();
  }

  public function getGenderText()
  {
    return $this->query()
      ->select('gender')
      ->results()
      ->single();
  }

  public function getGender()
  {
    $getCode = function($value) {
      $gender_mapping = [
        'female' => 'F',
        'male' => 'M',
        'unknown' => 'UNK',
      ];
      $code = @$gender_mapping[strtolower($value)] ?: 'UNK';
      return $code;
    };
    $genderCode = $this->getGenderCode();
    if($genderCode) return $genderCode;
    $genderText = $this->getGenderText();
    $code = $getCode($genderText);
    return $code;
  }
  
  public function getRaceCode()
  {
    return $this->query()
      ->select('#/extension/\d+')
      ->where('url', 'like', 'race$')
      ->select('code$')
      ->results()
      ->single();
  }

  public function getEthnicityCode()
  {
    return $this->query()
      ->select('#/extension/\d+')
      ->where('url', 'like', 'ethnicity$')
      ->select('code$')
      ->results()
      ->single();
  }
  
  public function getAddressLine()
  {
    return $this->query()
      ->select('#/address/\d+')
      ->where('use', '=', 'home')
      ->select('line')
      ->results()
      ->join(' ');
  }
  
  public function getAddressCity()
  {
    return $this->query()
      ->select('#/address/\d+')
      ->where('use', '=', 'home')
      ->select('city')
      ->results()
      ->single();
  }
  
  public function getAddressState()
  {
    return $this->query()
      ->select('#/address/\d+')
      ->where('use', '=', 'home')
      ->select('state')
      ->results()
      ->single();
  }
  
  public function getAddressPostalCode()
  {
    return $this->query()
      ->select('#/address/\d+')
      ->where('use', '=', 'home')
      ->select('postalCode')
      ->results()
      ->single();
  }
  
  public function getAddressCountry()
  {
    return $this->query()
      ->select('#/address/\d+')
      ->where('use', '=', 'home')
      ->select('country')
      ->results()
      ->single();
  }
  
  public function getPhoneHome($index=0)
  {
    return $this->query()
      ->select('#/telecom/\d+')
      ->where('system', '=', 'phone')
      ->where('use', '=', 'home')
      ->select('value')
      ->results()
      ->single($index);
  }
  
  public function getPhoneMobile($index=0)
  {
    return $this->query()
      ->select('#/telecom/\d+')
      ->where('system', '=', 'phone')
      ->where('use', '=', 'mobile')
      ->select('value')
      ->results()
      ->single($index);
  }
  
  public function isDeceased() 
  {
    $deceasedDateTime = $this->getDeceasedDateTime();
    if(!empty($deceasedDateTime)) return true;
    return $this->getDeceasedBoolean();
  }

  public function getDeceasedBoolean()
  {
    return $this->query()
      ->select('#/deceasedBoolean')
      ->results()
      ->single();
  }
  
  public function getDeceasedDateTime()
  {
    return $this->query()
      ->select('#/deceasedDateTime')
      ->results()
      ->single();
  }
  
  public function getPreferredLanguage()
  {
    return $this->query()
      ->select('#/communication')
      ->where('preferred', '=', 'true')
      ->select('language/text')
      ->results()
      ->single();
  }
  
  public function getEmail($index=0)
  {
    return $this->query()
      ->select('#/telecom/\d+')
      ->where('system', '=', 'email')
      ->select('value')
      ->results()
      ->single($index);
  }

  /**
   * get a callable based on a mapping field
   *
   * @param string $field
   * @return callable
   */
  public function getCallable($field)
  {
    switch ($field) {
      case 'fhir_id':
        $callable = function() { return $this->getFhirID(); };
        break;
      case 'name-given':
        $callable = function() { return $this->getNameGiven(); };
        break;
      case 'name-family':
        $callable = function() { return $this->getNameFamily(); };
        break;
      case 'birthDate':
        $callable = function() { return $this->getBirthDate(); };
        break;
      case 'gender':
        $callable = function() { return $this->getGender(); };
        break;
      case 'gender-code':
        $callable = function() { return $this->getGenderCode(); };
        break;
      case 'gender-text':
        $callable = function() { return $this->getGenderText(); };
        break;
      case 'race':
        $callable = function() { return $this->getRaceCode(); };
        break;
      case 'ethnicity':
        $callable = function() { return $this->getEthnicityCode(); };
        break;
      case 'address-line':
        $callable = function() { return $this->getAddressLine(); };
        break;
      case 'address-city':
        $callable = function() { return $this->getAddressCity(); };
        break;
      case 'address-state':
        $callable = function() { return $this->getAddressState(); };
        break;
      case 'address-postalCode':
        $callable = function() { return $this->getAddressPostalCode(); };
        break;
      case 'address-country':
        $callable = function() { return $this->getAddressCountry(); };
        break;
      case 'phone-home':
        $callable = function() { return $this->getPhoneHome(); };
        break;
      case 'phone-home-2':
        $callable = function() { return $this->getPhoneHome(1); };
        break;
      case 'phone-home-3':
        $callable = function() { return $this->getPhoneHome(2); };
        break;
      case 'phone-mobile':
        $callable = function() { return $this->getPhoneMobile(); };
        break;
      case 'phone-mobile-2':
        $callable = function() { return $this->getPhoneMobile(1); };
        break;
      case 'phone-mobile-3':
        $callable = function() { return $this->getPhoneMobile(2); };
        break;
      case 'deceasedBoolean':
        $callable = function() { return intval($this->isDeceased()); };
        break;
      case 'deceasedDateTime':
        $callable = function() { return $this->getDeceasedDateTime(); };
        break;
      case 'preferred-language':
        $callable = function() { return $this->getPreferredLanguage(); };
        break;
      case 'email':
        $callable = function() { return $this->getEmail(); };
        break;
      case 'email-2':
        $callable = function() { return $this->getEmail(1); };
        break;
      case 'email-3':
        $callable = function() { return $this->getEmail(2); };
        break;
      default:
        $callable =function() { return ''; };
        break;
      }
      return $callable;
  }

  public function getData()
  {
    $data = [
      'fhir_id'             =>  $this->getFhirID(),
      'name-given'          =>  $this->getNameGiven(),
      'name-family'         =>  $this->getNameFamily(),
      'birthDate'           =>  $this->getBirthDate(),
      'gender'              =>  $this->getGender(),
      'gender-code'         =>  $this->getGenderCode(),
      'gender-text'         =>  $this->getGenderText(),
      'race'                =>  $this->getRaceCode(),
      'ethnicity'           =>  $this->getEthnicityCode(),
      'address-line'        =>  $this->getAddressLine(),
      'address-city'        =>  $this->getAddressCity(),
      'address-state'       =>  $this->getAddressState(),
      'address-postalCode'  =>  $this->getAddressPostalCode(),
      'address-country'     =>  $this->getAddressCountry(),
      'phone-home'          =>  $this->getPhoneHome(),
      'phone-home-2'        =>  $this->getPhoneHome(1),
      'phone-home-3'        =>  $this->getPhoneHome(2),
      'phone-mobile'        =>  $this->getPhoneMobile(),
      'phone-mobile-2'      =>  $this->getPhoneMobile(1),
      'phone-mobile-3'      =>  $this->getPhoneMobile(2),
      'deceasedBoolean'     =>  intval($this->isDeceased()), // cast to int
      'deceasedBooleanRaw'  =>  $this->getDeceasedBoolean(), // cast to int
      'deceasedDateTime'    =>  $this->getDeceasedDateTime(),
      'preferred-language'  =>  $this->getPreferredLanguage(),
      'email'               =>  $this->getEmail(),
      'email-2'             =>  $this->getEmail(1),
      'email-3'             =>  $this->getEmail(2),
    ];
    return $data;
  }
  
}