<?php
namespace Vanderbilt\REDCap\Classes\Fhir\Utility;

class FileCache
{
  /**
   * use a namespace for better organization
   * of cached data
   *
   * @var string
   */
  private $namespace;

  /**
   * path to the directory
   * where cache files will be stored
   *
   * @var string
   */
  private $cacheDir;

  public function __construct($namespace='', $cacheDir=APP_PATH_TEMP)
  {
    $this->namespace = $namespace;
    // $cacheDir = sys_get_temp_dir();
    $this->cacheDir = realpath($cacheDir);
  }

  /**
   * get the name and path of the file
   * where the key is stored
   *
   * @param string $key
   * @return string
   */
  private function getFileName($key)
  {
    $filename = sprintf("%s.cache", md5($this->namespace.$key));
    $path = $this->cacheDir.DIRECTORY_SEPARATOR.$filename;
    return $path;
  }

  private function delete($key)
  {
    $filename = $this->getFileName($key);
    unlink($filename);
  }

  /**
   * set a variable
   *
   * @param string $key
   * @param mixed $data
   * @param int $ttl seconds to live (default to 15 minutes)
   * @return void
   */
  function set($key, $data, $ttl=900)
  {
    $fileHandle = fopen($this->getFileName($key),'w');
    if (!$fileHandle) throw new \Exception('Could not write to cache');

    // serialize data and its lifetime
    $lifespan = time()+$ttl;
    $data = serialize([$lifespan, $data]);
    if (fwrite($fileHandle, $data)===false) throw new \Exception('Could not write to cache');
    fclose($fileHandle);
  }

  /**
   * get a data from the cache.
   * do not return if the value has expired
   *
   * @param string $key
   * @return mixed
   */
  public function get($key)
  {
    $filename = $this->getFileName($key);
    if (!file_exists($filename) || !is_readable($filename)) return false;

    $data = file_get_contents($filename);

    $data = @unserialize($data);
    $ttl = @$data[0];
    if (!$data || (time() > $ttl)) {
       $this->delete($key);
       return;
    }
    
    return @$data[1];
  }

 }