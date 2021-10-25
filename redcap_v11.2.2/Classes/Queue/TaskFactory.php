<?php
namespace Vanderbilt\REDCap\Classes\Queue;

use Opis\Closure\SerializableClosure;
use Vanderbilt\REDCap\Classes\Queue\Message;
use Vanderbilt\REDCap\Classes\Queue\TaskInterface;
use Vanderbilt\REDCap\Classes\Queue\Tasks\Closure;

class TaskFactory
{

    const TASK_NAMESPACE = 'Vanderbilt\\REDCap\\Classes\\Queue\\Tasks';

    /**
     * save a referecne to the message that created the task
     *
     * @var [type]
     */
    private $message;
    
    /**
     * load a Task Class
     *
     * @param string $class_name
     * @throws \Exception if file does not exists
     * @return boolean
     */
    private static function loadClass($class_name)
    {
        $path = join(DIRECTORY_SEPARATOR, array(dirname(__FILE__), 'Tasks', $class_name.".php"));
        if (file_exists($path)) {
            include_once($path);
            return true;
        } else {
            throw new \Exception("The class {$class_name} is not loadable");
        }
    }

    /**
     * compose the class name using the key in the message
     *
     * @param string $key
     * @return string
     */
    private static function getClassName($key)
    {
        $class_name = self::TASK_NAMESPACE.'\\'.$key;
        return $class_name;
    }

    /**
     * create a task from a message
     *
     * @param Message $message
     * @return TaskInterface
     */
    public static function create($message)
    {
        $data = $message->getData();
        // check if it is a closure
        if($data instanceof SerializableClosure) {
            $task = new Closure($message);
            return $task;
        }
        $key = $message->getKey();
        throw new \Exception("Cannot implement a task from the provided message '{$key}'", 1);
    }

}