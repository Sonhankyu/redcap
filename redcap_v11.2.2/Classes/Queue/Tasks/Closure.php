<?php
namespace Vanderbilt\REDCap\Classes\Queue\Tasks;

use Opis\Closure\SerializableClosure;
use Vanderbilt\REDCap\Classes\Queue\TaskInterface;

class Closure implements TaskInterface
{

    /**
     * Closure
     *
     * @var \Closure
     */
    private $closure;

    public function __construct($message)
    {
        $this->message = $message;
        $data = $message->getData();
        $key = $message->getKey();
        // check if it is a closure

        if(!$data instanceof SerializableClosure) {
            throw new \Exception("Cannot implement a task from the provided message '{$key}'", 1);
        }

        $closure = $data->getClosure();
        if(!is_callable($closure)) throw new \Exception("The provided closure is not a valid callable", 400);
        $this->closure = $closure;
    }

    public function handle()
    {
        $closure = $this->closure;
        return $closure($this->message);
    }

}