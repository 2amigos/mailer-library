<?php
namespace Da\Mailer\Queue\Backend\Sqs;

use BadMethodCallException;
use Da\Mailer\Event\EventHandlerTrait;
use Da\Mailer\Model\MailJob;
use Da\Mailer\Model\MailMessage;

class SqsMailJob extends MailJob
{
    use EventHandlerTrait;
    /**
     * @var string
     */
    private $receiptHandle;
    /**
     * @var MailMessage|string the message to store
     */
    private $message;
    /**
     * @var int
     */
    private $delaySeconds;
    /**
     * @var int between 0 and 900 seconds
     */
    private $visibilityTimeout;
    /**
     * @var bool
     */
    private $deleted = false;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    /**
     * @return bool
     */
    public function isNewRecord()
    {
        return $this->getId() === null || $this->receiptHandle === null;
    }

    /**
     * @return string
     */
    public function getReceiptHandle()
    {
        return $this->receiptHandle;
    }

    /**
     * @param string $receiptHandle
     */
    public function setReceiptHandle($receiptHandle)
    {
        $this->receiptHandle = $receiptHandle;
    }

    /**
     * @return int
     */
    public function getDelaySeconds()
    {
        return $this->delaySeconds;
    }

    /**
     * @param int $delaySeconds
     */
    public function setDelaySeconds($delaySeconds)
    {
        if ($delaySeconds < 0 || $delaySeconds > 900) {
            throw new BadMethodCallException('Delay seconds must be between 0 and 900 seconds interval');
        }
        $this->delaySeconds = $delaySeconds;
    }

    /**
     * @return int
     */
    public function getVisibilityTimeout()
    {
        return $this->message;
    }

    /**
     * @param int $visibilityTimeout
     */
    public function setVisibilityTimeout($visibilityTimeout)
    {
        $this->visibilityTimeout = $visibilityTimeout;
    }

    /**
     * @return bool
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * @param bool $deleted
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }
}
