<?php
namespace Da\Mailer\Queue\Cli;

use Da\Mailer\Event\EventHandlerTrait;
use Da\Mailer\Mailer;
use Da\Mailer\Model\MailMessage;
use Exception;

class MailMessageWorker
{
    use EventHandlerTrait;

    /**
     * @var Mailer instance to the send the mails
     */
    private $mailer;
    /**
     * @var MailMessage the mail message to be sent
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
     * Sends the MailMessage. It does triggers the following events:.
     *
     * - onSuccess: If the sending has been successful
     * - onFailure: If the sending has failed
     *
     * The events need to be configured by attaching a handler to them.
     *
     * @see EventHandlerTrait
     */
    public function run()
    {
        $event = 'onSuccess';

        try {
            $sentMessage = $this->mailer->send($this->mailMessage);
            if (is_null($sentMessage)) {
                $event = 'onFailure';
            }
        } catch (Exception $e) {
            $event = 'onFailure';
        }
        $this->trigger($event, [$this->mailMessage, $sentMessage ?? null]);
    }
}
