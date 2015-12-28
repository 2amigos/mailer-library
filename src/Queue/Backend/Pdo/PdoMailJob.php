<?php
namespace Da\Mailer\Queue\Backend\Pdo;

use Da\Mailer\Event\EventHandlerTrait;
use Da\Mailer\Model\AbstractMailObject;
use Da\Mailer\Queue\Backend\MailJobInterface;

class PdoMailJob extends AbstractMailObject implements MailJobInterface
{
    use EventHandlerTrait;

    /**
     * State new
     */
    const STATE_NEW = 'N';
    /**
     * State active or in process
     */
    const STATE_ACTIVE = 'A';
    /**
     * State completed
     */
    const STATE_COMPLETED = 'C';
    /**
     * @var int
     */
    private $id;
    /**
     * @var string
     */
    private $message;
    /**
     * @var int
     */
    private $timeToSend;
    /**
     * @var int number of attempts
     */
    private $attempt = 0;
    /**
     * @var string
     */
    private $state = self::STATE_NEW;

    public function __construct(array $config = []) {
        parent::__construct($config);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $anId
     */
    public function setId($anId)
    {
        $this->id = $anId;
    }

    /**
     * @return bool
     */
    public function isNewRecord()
    {
        return $this->id === null;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return int
     */
    public function getTimeToSend()
    {
        return $this->timeToSend ?: date('Y-m-d H:i:s', time());
    }

    /**
     * @param int $time
     */
    public function setTimeToSend($time)
    {
        $this->timeToSend = $time;
    }

    /**
     * @return int
     */
    public function getAttempt()
    {
        return $this->attempt;
    }

    public function setAttempt($attempt)
    {
        $this->attempt = $attempt;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Increments attempt by one
     */
    public function incrementAttempt()
    {
        $this->attempt += 1;
    }

    /**
     * Marks the state as completed. After marking the instance as completed, we should call the
     * `PdoQueueStoreAdapter::ack()` method to update the database with new status.
     */
    public function markAsCompleted()
    {
        $this->state = self::STATE_COMPLETED;
    }

    /**
     * @return bool
     */
    public function isCompleted()
    {
        return $this->state === self::STATE_COMPLETED;
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
