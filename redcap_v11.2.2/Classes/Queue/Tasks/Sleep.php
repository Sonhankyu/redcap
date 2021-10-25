<?php
namespace Vanderbilt\REDCap\Classes\Queue\Tasks;

use Vanderbilt\REDCap\Classes\Queue\TaskInterface;

class Sleep implements TaskInterface
{

    private $time;

    public function __construct($data)
    {
        $this->time = $data['sleep'] ?: 10;
    }

    public function handle()
    {
        sleep($this->time);
    }

}