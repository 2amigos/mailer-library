<?php
namespace Da\Mailer\Security;

use Da\Mailer\Model\MailMessage;


interface CypherInterface
{
    /**
     * @param MailMessage $mailMessage
     *
     * @return string
     */
    public function encodeMailMessage(MailMessage $mailMessage);
    /**
     * @param $encodedMailMessage
     *
     * @return MailMessage
     */
    public function decodeMailMessage($encodedMailMessage);
}
