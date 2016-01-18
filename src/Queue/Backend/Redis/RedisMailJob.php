<?php
namespace Da\Mailer\Queue\Backend\Redis;

use Da\Mailer\Event\EventHandlerTrait;
use Da\Mailer\Model\MailJob;

class RedisMailJob extends MailJob
{
    use EventHandlerTrait;

    /**
     * @var int the unix timestamp the job should be processed
     */
    private $timeToSend;

    /**
     * @return int the timestamp
     */
    public function getTimeToSend()
    {
        return $this->timeToSend;
    }

    /**
     * @param int $timestamp
     */
    public function setTimeToSend($timestamp)
    {
        $this->timeToSend = $timestamp;
    }
}
