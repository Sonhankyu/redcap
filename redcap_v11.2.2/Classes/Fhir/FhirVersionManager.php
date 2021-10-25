<?php
namespace Vanderbilt\REDCap\Classes\Fhir;

use Session;
use Vanderbilt\REDCap\Classes\Fhir\Endpoints\ConformanceStatement;
use Vanderbilt\REDCap\Classes\Fhir\Endpoints\DSTU2\EndpointFactory as EndpointFactory_DSTU2;
use Vanderbilt\REDCap\Classes\Fhir\Endpoints\R4\EndpointFactory as EndpointFactory_R4;
use Vanderbilt\REDCap\Classes\Fhir\Endpoints\EndpointFactoryInterface;
use Vanderbilt\REDCap\Classes\Fhir\FhirMetadata\FhirMetadataSource;
use Vanderbilt\REDCap\Classes\Fhir\Resources\ConformanceStatement as ResourcesConformanceStatement;
use Vanderbilt\REDCap\Classes\Fhir\Resources\DSTU2\ResourceFactory as ResourceFactory_DSTU2;
use Vanderbilt\REDCap\Classes\Fhir\Resources\R4\ResourceFactory as ResourceFactory_R4;
use Vanderbilt\REDCap\Classes\Fhir\Utility\FileCache;

/**
 * Factory for factories.
 * Get the factory that will build endpoints for a specific FHIR version.
 */
class FhirVersionManager
{

  static private $instance;
  /**
   * major.minor.patch
   *
   * @var string
   */
  private $fhir_version;
  
  /**
   * DSTU2, R4, etc...
   *
   * @var string
   */
  private $fhir_code;
  
  /**
   * FHIR base URL
   *
   * @var string
   */
  private $base_url;
  
  /**
   * the conformance statement resource
   *
   * @var ResourcesConformanceStatement
   */
  private $conformance_statement;

  /**
   * FHIR versions
   */
  const FHIR_DSTU1 = 'DSTU1';
  const FHIR_DSTU2 = 'DSTU2';
  const FHIR_STU3 = 'STU3';
  const FHIR_R4 = 'R4';
  const SUPPORTED_VERSIONS = [self::FHIR_DSTU2,self::FHIR_R4];

  /**
   * manage tools and resources for a FHIR version
   *
   * @param string $fhir_version
   */
  private function __construct($base_url)
  {
    $this->base_url = $base_url;
    $fhir_version = $this->getFhirVersion();
    $fhir_code = $this->getFhirCodeFromVersion($fhir_version);
    if(!in_array($fhir_code, self::SUPPORTED_VERSIONS)) throw new \Exception("The FHIR version '$fhir_code' is not supported.", 400);
    $this->fhir_version = $fhir_version;
    $this->fhir_code = $fhir_code;
    static::$instance = $this;
  }

  public static function getInstance($base_url=null)
  {
    if(!static::$instance) {
      if(!$base_url) {
        $configVals = \System::getConfigVals();
        $base_url = @$configVals['fhir_endpoint_base_url'];
      }
      static::$instance = new self($base_url);
    }
    return static::$instance;
  }

  public function getBaseUrl()
  {
    return $this->base_url;
  }

  public function getVersion()
  {
    return $this->fhir_version;
  }

  public function getFhirCode()
  {
    return $this->fhir_code;
  }

  /**
   * get the code name of the FHIR version based on its numeric code
   * @var string $fhir_version numeric version of the FHIR service (major.minor.patch)
   * @return string
   */
  public function getFhirCodeFromVersion($fhir_version)
  {
    preg_match("#(?<major>\d+)\.(?<minor>\d+)(?:\.(?<patch>\d+))?#", $fhir_version, $matches);
    $major = @$matches['major'];
    $version_mapping = [
      '0' => self::FHIR_DSTU1,
      '1' => self::FHIR_DSTU2,
      '3' => self::FHIR_STU3,
      '4' => self::FHIR_R4,
    ];
    return @$version_mapping[$major];
  }

  /**
   * get a factory based on the FHIR code version
   *
   * @param string $base_url base URL for the FHIR requests
   * @return EndpointFactoryInterface
   */
  public function getEndpointFactory()
  {
    switch ($this->fhir_code) {
      case self::FHIR_DSTU2:
        $factory = new EndpointFactory_DSTU2($this->base_url);
        break;
      case self::FHIR_R4:
        $factory = new EndpointFactory_R4($this->base_url);
        break;
      default:
        $factory = null;
        break;
    }
    return $factory;
  }

  public function getResourceFactory()
  {
    switch ($this->fhir_code) {
      case self::FHIR_DSTU2:
        $factory = new ResourceFactory_DSTU2();
        break;
      case self::FHIR_R4:
        $factory = new ResourceFactory_R4();
        break;
      default:
        $factory = null;
        break;
    }
    return $factory;
  }

  /**
   * TODO
   *
   * @return void
   */
  public function getParserFactory()
  {
    switch ($this->fhir_version) {
      case self::FHIR_DSTU2:
        $factory = null;
        break;
      case self::FHIR_R4:
        $factory = null;
        break;
      default:
        $factory = null;
        break;
    }
    return $factory;
  }

  private function fetchConformanceStatement()
  {
    try {
      $conformanceStatementEndpoint = new ConformanceStatement($this->base_url);
      $request = $conformanceStatementEndpoint->getMetadata();
			$response = $request->send();
			$payload = json_decode($response, true);
			return $payload;
		} catch (\Exception $e) {
			$message = $e->getMessage();
			throw new \Exception("Could not make a successful call to the Conformance Statement at {$request->getURL()}.\n\r{$message}", 400);
		}
  }

  private function getConformanceStatement()
  {
    if(!$this->conformance_statement) {
      $payload = $this->fetchConformanceStatement();
      $this->conformance_statement = new ResourcesConformanceStatement($payload);
    }
    return $this->conformance_statement;
  }

  /**
   * get the current FHIR version from memory
   * or extract it from the conformance statement
   *
   * @return string
   */
  public function getFhirVersion()
  {
    $fileCache = new FileCache(__CLASS__);
    $key = 'fhir_version'.$this->base_url;
    $fhir_version = $fileCache->get($key);
    if(!$fhir_version) {
      $conformance_statement = $this->getConformanceStatement();
      $fhir_version = $conformance_statement->getFhirVersion();
      $fileCache->set($key, $fhir_version);
    }
    return $fhir_version;
  }

  /**
   * create a FhirMetadataGenerator 
   *
   * @return FhirMetadataSource
   */
  public function getFhirMetadataSource()
  {
    return new FhirMetadataSource($this->fhir_code);
  }

  /**
   * helper to get the fhir metadata list
   *
   * @return array
   */
  public static function getFhirMetadata()
  {
    $instance = self::getInstance();
    $metadataManager = $instance->getFhirMetadataSource();
    return $metadataManager->getList();
  }
  
}