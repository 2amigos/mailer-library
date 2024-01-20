<?php

namespace Da\Mailer\Builder;

use Da\Mailer\Mail\Dto\EmailAddress;
use Da\Mailer\Mail\Dto\File;
use Da\Mailer\Model\MailMessage;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class MessageBuilder extends Buildable
{
    /**
     * @param MailMessage $mailMessage
     * @return Email
     * @throws \Exception
     */
    public static function make($mailMessage = null): Email
    {
        $message = new Email();
        $message->subject($mailMessage->subject);

        self::setFrom($mailMessage, $message);
        self::setTo($mailMessage, $message);
        self::setCc($mailMessage, $message);
        self::setBcc($mailMessage, $message);
        self::setHtml($mailMessage, $message);
        self::setText($mailMessage, $message);
        self::setAttachments($mailMessage, $message);

        return $message;
    }

    /**
     * @param string|array|EmailAddress $emails
     * @param string $method
     * @param Email $message
     * @return void
     */
    protected static function setEmail($emails, string $method, Email $message)
    {
        if (is_string($emails)) {
            $message->{$method}($emails);

            return;
        }

        if (is_array($emails)) {
            foreach ($emails as $email) {
                if ($email instanceof EmailAddress) {
                    $email = $email->parseToMailer();
                }

                $message->{'add' . strtoupper($method)}($email);
            }

            return;
        }

        $message->{$method}($emails->parseToMailer());
    }

    /**
     * @param MailMessage $mailMessage
     * @param Email $message
     * @return void
     */
    public static function setFrom(MailMessage $mailMessage, Email $message): void
    {
        self::setEmail($mailMessage->from, 'from', $message);
    }

    /**
     * @param MailMessage $mailMessage
     * @param Email $message
     * @return void
     */
    public static function setTo(MailMessage $mailMessage, Email $message): void
    {
        self::setEmail($mailMessage->to, 'to', $message);
    }

    /**
     * @param MailMessage $mailMessage
     * @param Email $message
     * @return void
     */
    protected static function setCc(MailMessage $mailMessage, Email $message)
    {
        if (! is_null($mailMessage->cc)) {
            self::setEmail($mailMessage->cc, 'cc', $message);
        }
    }

    /**
     * @param MailMessage $mailMessage
     * @param Email $message
     * @return void
     */
    protected static function setBcc(MailMessage $mailMessage, Email $message)
    {
        if (! is_null($mailMessage->bcc)) {
            self::setEmail($mailMessage->bcc, 'bcc', $message);
        }
    }

    /**
     * @param MailMessage $mailMessage
     * @param Email $message
     * @return void
     * @throws \Exception
     */
    protected static function setHtml(MailMessage $mailMessage, Email $message)
    {
        $config = self::getConfig();
        $html = $mailMessage->bodyHtml;

        if (isset($html)) {
            $html = self::extractBodyMessage($html);

            $message->html($html, $config['mail-charset']);
        }
    }

    /**
     * @param MailMessage $mailMessage
     * @param Email $message
     * @return void
     * @throws \Exception
     */
    protected static function setText(MailMessage $mailMessage, Email $message)
    {
        $config = self::getConfig();
        $text = $mailMessage->bodyText;

        if (isset($mailMessage->bodyText)) {
            $text = self::extractBodyMessage($text);

            $message->text($text, $config['mail-charset']);
        }
    }

    /**
     * @param MailMessage $mailMessage
     * @param Email $message
     * @return void
     */
    protected static function setAttachments(MailMessage $mailMessage, Email $message)
    {
        /** @var File $attachment */
        foreach ($mailMessage->getAttachments() as $attachment) {
            $message->attachFromPath($attachment->getPath(), $attachment->getName());
        }
    }

    /**
     * @param string $message
     * @return false|resource|string
     */
    protected static function extractBodyMessage(string $message)
    {
        return realpath($message)
            ? fopen($message, 'r')
            : $message;
    }
}
