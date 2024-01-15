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

        return $message;
    }

    /**
     * @param MailMessage $mailMessage
     * @param Email $message
     * @return void
     */
    public static function setFrom(MailMessage $mailMessage, Email $message): void
    {
        /** @var string|EmailAddress $from */
        $from = $mailMessage->from;

        if (is_string($from)) {
            $message->from($from);

            return;
        }

        $message->from($from->getEmail(), $from->getName());
    }

    /**
     * @param MailMessage $mailMessage
     * @param Email $message
     * @return void
     */
    public static function setTo(MailMessage $mailMessage, Email $message): void
    {
        /** @var string|EmailAddress $to */
        $to = $mailMessage->to;

        if (is_string($to)) {
            $message->to($to);

            return;
        }

        $message->to($to->getEmail(), $to->getName());
    }

    /**
     * @param MailMessage $mailMessage
     * @param Email $message
     * @return void
     */
    protected static function setCc(MailMessage $mailMessage, Email $message)
    {
        /** @var string|null|EmailAddress $cc */
        $cc = $mailMessage->cc;

        if (empty($cc)) {
            return;
        }

        if (is_string($cc)) {
            $message->cc($cc);

            return;
        }

        $message->cc($cc->getEmail(), $cc->getName());
    }

    /**
     * @param MailMessage $mailMessage
     * @param Email $message
     * @return void
     */
    protected static function setBcc(MailMessage $mailMessage, Email $message)
    {
        /** @var string|null|EmailAddress $bcc */
        $bcc = $mailMessage->cc;

        if (empty($bcc)) {
            return;
        }

        if (is_string($bcc)) {
            $message->bcc($bcc);

            return;
        }

        $message->bcc($bcc->getEmail(), $bcc->getName());
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
        foreach ($mailMessage->attachments ?? [] as $attachment) {
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
