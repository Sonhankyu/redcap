<?php
namespace Vanderbilt\REDCap\Classes\Queue;

use Vanderbilt\REDCap\Classes\Queue\TaskFactory;

class Worker {

    const LOG_OOBJECT_TYPE = 'QUEUE_WORKER';

    /**
     * maximum number of messages that a worker can process
     *
     * @var integer
     */
    private $max_processing = 100;
    private $max_processing_per_type = 10;

    const MAX_EXECUTION_TIME = 60; //seconds

    /**
     * Constructor: Setup our enviroment, load the queue and then
     * process the message.
     */
    public function __construct($max_processing=null, $max_processing_per_type = null)
    {
        # Get the queue
        $this->max_processing = intval($max_processing) ?: 500;
        $this->max_processing_per_type = intval($max_processing_per_type) ?: 50;
    }

    public function hasMessages()
    {
        $queue = Queue::getQueue();
        $current = ($queue)->current() ?: false;
        return $current !== false;
    }

    /**
     * amount of time after which the worker
     * should stop processing messages
     *
     * @return integer
     */
    private function getMaximumExecutionTime()
    {
        return self::MAX_EXECUTION_TIME*10;
    }

    /**
     * Process a message and handle the task
     *
     * @param Message $message
     * @return string
     */
    public function processMessage($message)
    {
        try {
            if(!Queue::checkMessageStatus($message, $status = Queue::STATUS_READY)) return $status; // skip if status is not 'ready' when processing
            $task = TaskFactory::create($message);
            $message_id = $message->getId();
            Queue::updateMessage($message_id, $status = Queue::STATUS_PROCESSING);
            $task->handle(); // start processing
            Queue::updateMessage($message_id, $status = Queue::STATUS_COMPLETED);
            \Logging::logEvent( $sql="", self::LOG_OOBJECT_TYPE, "MANAGE", $message_id, "", "Message ID '{$message_id}' has been successfully processed.");
            return $status;
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $message_id = $message->getId();
            Queue::updateMessage($message_id, $status = Queue::STATUS_ERROR);
            \Logging::logEvent( $sql="", self::LOG_OOBJECT_TYPE, "MANAGE", $message_id, "", "Error processing Message ID '{$message_id}'. {$error}");
            return $status;
        }
    }

    public function process()
    {
        $start_time = microtime(true);
        $max_execution_time = $this->getMaximumExecutionTime();

        while($message = Queue::getFirst()) {
            if(!($message instanceof Message)) continue;

            $key = $message->getKey();
            $total_processing = Queue::countActive();
            $total_processing_of_type = Queue::countActive($key);
            // exit if maximum number of concurrent processing is reached
            if($total_processing>=$this->max_processing || $total_processing_of_type>=$this->max_processing_per_type) break;

            // $data = $message->getData();
            // $callable = $data['callable'] ?: null;
            $status = $this->processMessage($message);
            
            $loop_time = microtime(true);
            $timediff = $loop_time - $start_time;
            // exit if the loop lasted more than maximum time
            if($timediff>=$max_execution_time) break;
        }
    }

}