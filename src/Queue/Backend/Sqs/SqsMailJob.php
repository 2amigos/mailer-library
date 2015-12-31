<?php
namespace Da\Mailer\Queue\Backend\Sqs;

use BadMethodCallException;
use Da\Mailer\Event\EventHandlerTrait;
use Da\Mailer\Model\AbstractMailObject;
use Da\Mailer\Queue\Backend\MailJobInterface;
use Da\Mailer\Model\MailMessage;

class SqsMailJob extends AbstractMailObject implements MailJobInterface
{
    use EventHandlerTrait;

    /**
     * @var MailMessage|string the message to store
     */
    private $message;
    /**
     * @var integer
     */
    private $delaySeconds;

    /**
     * @inheritdoc
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @inheritdoc
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getDelaySeconds()
    {
        return $this->delaySeconds;
    }

    /**
     * @param integer $delaySeconds
     */
    public function setDelaySeconds($delaySeconds)
    {
        if ($delaySeconds < 0 || $delaySeconds > 900) {
            throw new BadMethodCallException();
        }
        $this->delaySeconds = $delaySeconds;
    }
}
