<?php

namespace Da\Mailer\Queue\Backend\Pdo;

use Da\Mailer\Event\EventHandlerTrait;
use Da\Mailer\Model\MailJob;

class PdoMailJob extends MailJob
{
    use EventHandlerTrait;

/**
     * State new.
     */


    const STATE_NEW = 'N';
/**
     * State active or in process.
     */
    const STATE_ACTIVE = 'A';
/**
     * State completed.
     */
    const STATE_COMPLETED = 'C';
/**
     * @var string the date value to when to send the email when processing the queue from a daemon. The format is
     * `Y-m-d H:i:s`
     */
    private $timeToSend;
/**
     * @var string
     */
    private $state = self::STATE_NEW;
/**
     * {@inheritdoc}
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    /**
     * @return string
     */
    public function getTimeToSend()
    {
        return $this->timeToSend ?: date('Y-m-d H:i:s', time());
    }

    /**
     * @param string $date
     */
    public function setTimeToSend($date)
    {
        $this->timeToSend = $date;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Marks the state as completed. After marking the instance as completed, we should call the
     * `PdoQueueStoreAdapter::ack()` method to update the database with new status.
     */
    public function markAsCompleted()
    {
        $this->state = self::STATE_COMPLETED;
        parent::markAsCompleted();
    }

    /**
     * Marks the state as new. If we update the status back to 'N'ew, we could send it back to queue by using the
     * `PdoQueueStoreAdapter::ack()` method. That means even including a new time to be processed in the future by
     * setting the `$timeToSend` in a future date.
     */
    public function markAsNew()
    {
        $this->state = self::STATE_NEW;
    }
}
