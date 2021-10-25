<?php
namespace Vanderbilt\REDCap\Classes\Queue;

use Opis\Closure\SerializableClosure;

/**
 * Queues allow you to defer the processing of a time consuming task, such as sending an email,
 * until a later time.
 * Deferring these time consuming tasks drastically speeds up web requests to REDCap.
 * 
 * Each 'message' added to the queue is translated into a 'task' and processed by a 'worker'.
 * A cron job checks the queue every minute and creates a worker if there are messages 'ready' to be processed.
 * If a worker cannot process all 'messages' in a minute, another worker will help processing 'messages'.
 * A maximum of 5 workers can run at the same time, but this number can be increased.
 * If a worker has been working for more than 10 minutes it will not process any further message.
 * 
 * Tasks can be specialised classes that implement the TaskInterface or can be Closures.
 * Examples for both cases are in the 'ClinicalDataMartDataFetch' Job.
 * 
 */
class Queue
{

    const TABLE_NAME = 'redcap_queue';
    const LOG_OOBJECT_TYPE = 'QUEUE';
    /**
     * list of message status
     */
    const STATUS_READY = 'ready';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELED = 'canceled';
    const STATUS_ERROR = 'error';
  
    /**
     * getQueue: Returns a generator with Messages ready to be processed.
     *
     * @return \Generator
     */
    public static function getQueue($key=null, $limit=100)
    {
        $key_query_string = isset($key) ? sprintf("AND name='%s'", db_real_escape_string($key)) : '';
        $query_string = sprintf(
            "SELECT * FROM `%s`
            WHERE status = 'ready'
            %s
            ORDER BY id ASC
            LIMIT %u",
            self::TABLE_NAME,
            $key_query_string,
            $limit
        );
        $result = db_query($query_string);
        while($row = db_fetch_assoc($result)) {
            $message = new Message($row);
            yield $message;
        }
    }

    /**
     * get a message from the database
     *
     * @param int $message_id
     * @return Message|null
     */
    public static function getMessage($message_id)
    {
        $query_string = sprintf(
            "SELECT * FROM `%s` WHERE id = %u",
            self::TABLE_NAME, $message_id
        );
        $result = db_query($query_string);
        if($row=db_fetch_assoc($result)) {
            $message = new Message($row);
            return $message;
        }
        return;
    }

    /**
     * get the first element of the queue;
     *
     * @param string $key
     * @return Message
     */
    public static function getFirst($key=null)
    {
        $generator = self::getQueue($key);
        $message = $generator->current();
        return $message;
    }

    /**
     * get the amount of active (being processed) messages
     *
     * @param string $name if specified count only for specific messages
     * @return integer
     */
    public static function countActive($key=null)
    {
        $key_query = isset($type) ? sprintf("AND key='%s'", $key) : '';
        $query_string = sprintf(
            "SELECT COUNT(id) AS total
            FROM `%s`
            WHERE status='%s'
            %s
            GROUP BY status",
            self::TABLE_NAME,
            self::STATUS_PROCESSING,
            $key_query
        );
        $result = db_query($query_string);
        if($row=db_fetch_object($result)) return intval($row->total);
        return 0;
    }

    /**
     * addMessage: Given a key, store a new message into our queue.
     *
     * @param $key string - Reference to the message (PK)
     * @param $data array - Some data to pass into the message
     */
    public static function addMessage($key, $data = array()) {
        $status = self::STATUS_READY;
        if($data instanceof \Closure) $data = self::createSerializableClosure($data);
        $serialized_data = serialize($data);
        $now = date('Y-m-d H:i:s');
        $query_string = sprintf(
            "INSERT INTO `%s` (`key`, `status`, `data`, `created_at`, `updated_at`)
            VALUES ('%s', '%s', '%s', '%s', '%s')",
            self::TABLE_NAME,
            $key,
            $status,
            db_real_escape_string($serialized_data),
            $now,
            $now
        );
        $result = db_query($query_string);
        if($result && $id=db_insert_id()) {
            \Logging::logEvent( $sql=$query_string, self::LOG_OOBJECT_TYPE, "MANAGE", "", "", "Message added to the queue.");
            return true;
        }else {
            \Logging::logEvent( $sql=$query_string, self::LOG_OOBJECT_TYPE, "MANAGE", "", "", "Error adding message to the queue.");
            throw new \Exception("Error adding message to queue", 1);
        }
    }

    /**
     * Undocumented function
     *
     * @param \Closure $closure
     * @return void
     */
    private static function createSerializableClosure($closure)
    {
        return new SerializableClosure($closure);
    }

    public static function updateMessage($id, $status)
    {
        $now = date('Y-m-d H:i:s');
        $query_string = sprintf(
            "UPDATE `%s` SET
            `status`='%s', `updated_at`='%s'
            WHERE `id`='%u'",
            self::TABLE_NAME,
            $status, $now, $id
        );
        $result = db_query($query_string);
        if(!$result) {
            \Logging::logEvent( $sql=$query_string, self::LOG_OOBJECT_TYPE, "MANAGE", "", "", $message="Error updating the message status.");
            throw new \Exception($message, 400);
        }
        \Logging::logEvent( $sql=$query_string, self::LOG_OOBJECT_TYPE, "MANAGE", "", "", $message="Queue message updated.");
        return $result;
    }

    /**
     * get a message from the database
     *
     * @param string $key
     * @param array $status
     * @return Message|null
     */
    public static function getMessagesByKey($key, $status=[])
    {
        $getStatusQueryClause = function($list) {
            if(empty($list)) return '';
            $statusQueryClause = '\''.implode('\', \'', $list).'\'';
            return sprintf(' AND `status` IN (%s)', $statusQueryClause);
        };
        $query_string = sprintf(
            "SELECT * FROM `%s` WHERE `key` = %s",
            self::TABLE_NAME, checkNull($key)
        );
        $query_string .= $getStatusQueryClause($status);
        
        $result = db_query($query_string);
        $messages = [];
        while($row=db_fetch_assoc($result)) {
            $message = new Message($row);
            $messages[] = $message;
        }
        return $messages;
    }

    /**
     * check the status of a stored message
     *
     * @param Message $message
     * @param string $status
     * @return string|false
     */
    public static function checkMessageStatus($message, $status)
    {
        $id = $message->getId();
        $query_string = sprintf("SELECT status FROM `%s` WHERE id='%u'", self::TABLE_NAME, $id);
        $result = db_query($query_string);
        if($row=db_fetch_object($result)) return $row->status==$status;
        return false;
    }
}