<?php
namespace Vanderbilt\REDCap\Classes\Fhir\Endpoints;

use JsonSerializable;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Query;

/**
 * request action returned by one of the
 * FHIR endpoints interactions
 */
class FhirRequest implements JsonSerializable
{

  /**
   * HTTP method
   *
   * @var string
   */
  private $method;

  /**
   * request URL
   *
   * @var string
   */
  private $url;

  /**
   * associative array of options
   * @see https://docs.guzzlephp.org/en/stable/request-options.html
   * @var array ['headers', 'query', ...]
   */
  private $options;


  /**
   *
   * @param string $method
   * @param string $url
   */
  public function __construct($url, $method='GET', $options=[])
  {
    $this->method = $method;
    $this->url = $url;
    $this->options = $options;
  }

  /**
   * get data from a FHIR endpoint
   *
   * @param string $url
   * @param string $access_token
   * @param bool $debug show debug info for the request
   * @return string HTTP response body
   */
  public function send($access_token=null, $debug=false)
  {
    $default_options = [
      'headers' => [
        'Accept' => 'application/json',
        'Content-Type' => 'application/x-www-form-urlencoded',
      ],
      'debug' => $debug,
      'query' => [],
    ];
    if($access_token) $default_options['headers']['Authorization'] = "Bearer {$access_token}";

    $http_options = array_merge_recursive($this->options, $default_options);
    
    /**
     * merge the query params in the URL (if any)
     * with those in the query array
     */
    $mergeUrlQueryParams = function($query_params=[]) {
      $url_query = parse_url($this->url, PHP_URL_QUERY);
      if(empty($url_query)) return $query_params;
      $parsed_query_string = Query::parse($url_query);
      foreach ($parsed_query_string as $key => $value) {
        if(!array_key_exists($key, $query_params)) {
          $query_params[$key] = $value;
        }
      }
      return $query_params;
    };
    /**
     * transform the query array to string
     */
    $queryParamsToString = function($query_params=[]) {
      if(!is_array($query_params)) return;
      $query_string = Query::build($query_params, PHP_QUERY_RFC1738);
      $query_params = $query_string;
      return $query_params;
    };
    $http_options['query'] = $mergeUrlQueryParams(@$http_options['query']);
    $http_options['query'] = $queryParamsToString(@$http_options['query']);

    $response = \HttpClient::request($this->method, $this->url, $http_options);
    return $response->getBody();
  }

  /**
   * set the HTTP options to send with the request
   *
   * @param array $options
   * @return void
   */
  public function setOptions($options)
  {
    $this->options = $options;
  }

  /**
   * get the HTTP options to send with the request
   *
   * @return array
   */
  public function getOptions() { return $this->options; }

  /**
   * getter for the method
   *
   * @return string
   */
  public function getMethod() { return $this->method; }

  /**
   * getter for the URL
   *
   * @return string
   */
  public function getURL() { return $this->url; }


  public function jsonSerialize()
  {
    return [
      'url' => $this->getURL(),
      'method' => $this->getMethod(),
      'options' => $this->getOptions(),
    ];
  }

}