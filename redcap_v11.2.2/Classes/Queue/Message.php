<?php
namespace Vanderbilt\REDCap\Classes\Queue;

/**
 * The 'Queue' class will be our simple way to reference
 * the queue regardless of which stage we are at.
 * We're defining a constant arbitary integer that we'll use as the queue
 * identifier and two integer values that we will use to reference
 * the type of message in the queue.
 */
class Message
{
    private $properties = [
        'id' =>null,
        'key' =>null,
        'data' =>null,
        'created_at' =>null,
        'updated_at' =>null,
    ];

    public function __construct($params)
    {
        $data = $params['data'];
        if(is_string($data)) {
            $params['data'] = unserialize($data);
        }
        foreach ($params as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * getKey: Returns the key
     */
    public function getId() {
        return $this->id;
    }
    
    /**
     * getKey: Returns the key
     */
    public function getKey() {
        return $this->key;
    }

    /**
     * getKey: Returns the key
     */
    public function getData() {
        return $this->data;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    public function __get($name) {
        if(array_key_exists($name, $this->properties)) {
            return $this->properties[$name];
        }

        $trace = debug_backtrace();
            trigger_error(
            'Undefined property via __get(): ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);
        return null;
    }

    public function __set($name, $value) {
        if(!array_key_exists($name, $this->properties)) return;
        $this->properties[$name] = $value;
    }
}