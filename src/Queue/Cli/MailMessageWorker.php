<?php

namespace Da\Mailer\Queue\Cli;

use Da\Mailer\Mailer;
use Da\Mailer\Event\EventHandlerTrait;
use Da\Mailer\Model\MailMessage;
use Exception;

/**
 * MailMessageWorker.
 */
class MailMessageWorker
{
    use EventHandlerTrait;

    /**
     * @var Mailer
     */
    private $mailer;
    /**
     * @var MailMessage
     */
    private $mailMessage;

    /**
     * MailMessageWorker constructor.
     *
     * @param Mailer $mailer
     * @param MailMessage $mailMessage
     */
    public function __construct(Mailer $mailer, MailMessage $mailMessage)
    {
        $this->mailer = $mailer;
        $this->mailMessage = $mailMessage;
    }

    /**
     *
     */
    public function run()
    {
        $failedRecipients = [];
        $event = 'onSuccess';

        try {
            $failedRecipients = $this->mailer->sendSwiftMessage($this->mailMessage->asSwiftMessage());
            if (!empty($failedRecipients)) {
                $event = 'onFailure';
            }
        } catch (Exception $e) {
            $event = 'onFailure';
            $failedRecipients[] = $this->mailMessage->to;
        }
        $this->trigger($event, [$this->mailMessage, $failedRecipients]);
    }
}
