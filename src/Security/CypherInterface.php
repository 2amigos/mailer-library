<?php
namespace Da\Mailer\Security;

use Da\Mailer\Model\MailMessage;

interface CypherInterface
{
    /**
     * Encodes a MailMessage instance.
     *
     * @param MailMessage $mailMessage
     *
     * @return string the encoded object
     */
    public function encodeMailMessage(MailMessage $mailMessage);

    /**
     * Decodes a string into a MailMessage instance.
     *
     * @param string $encodedMailMessage the encoded MailMessage as a string
     *
     * @return MailMessage the decoded instance
     */
    public function decodeMailMessage($encodedMailMessage);
}
