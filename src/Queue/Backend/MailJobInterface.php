<?php
namespace Da\Mailer\Queue\Backend;

use Da\Mailer\Model\MailMessage;

interface MailJobInterface
{
    /**
     * @param MailMessage|string $message
     */
    public function setMessage($message);

    /**
     * @return MailMessage|string
     */
    public function getMessage();
}
