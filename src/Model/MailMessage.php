<?php

namespace Da\Mailer\Model;

use Da\Mailer\Builder\MailJobBuilder;
use Da\Mailer\Builder\QueueBuilder;
use Da\Mailer\Mail\Dto\File;
use JsonSerializable;

class MailMessage extends AbstractMailObject implements JsonSerializable
{
    /**
     * @var array the transport options. They are different according to the transport type chosen. For example:
     *
     * SmtpTransport may contain:
     *
     * ```
     * [
     *  'username' => 'Obiwoan',
     *  'password' => 'Kenobi',
     *  'encryption' => 'ssl',
     *  'authMode' => 'Plain'
     * ]
     * ```
     *
     * MailTransport may contain:
     * ```
     * ['f%s']
     * ```
     *
     * SendMailTransport may contain:
     * ```
     * ['/usr/sbin/sendmail -bs']
     * ```
     */
    public $transportOptions = [];
/**
     * @var string the transport type. It could be TransportInterface::TYPE_SMTP, TransportInterface::TYPE_MAIL, o
     * TransportInterface::TYPE_SEND_MAIL.
     */
    public $transportType;
/**
     * @var string the mail server address.
     */
    public $host;
/**
     * @var int the mail server port.
     */
    public $port;
/**
     * @var array|string the from address/es
     */
    public $from;
/**
     * @var array|string the to address/es
     */
    public $to;
/**
     * @var array|string the cc address/es
     */
    public $cc;
/**
     * @var array|string the bcc address/es
     */
    public $bcc;
/**
     * @var string the subject of the mail message
     */
    public $subject;
/**
     * @var string the body html of the mail message
     */
    public $bodyHtml;
/**
     * @var string the body
     */
    public $bodyText;
/**
     * @var array|null the file paths to attach to the Swift_Message instance if `asSwiftMessage()` is called
     */
    protected $attachments;
/**
     * {@inheritdoc}
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    /**
     * @param array $config
     * @return MailMessage
     */
    public static function make(array $config)
    {
        return new self($config);
    }

    /**
     * Specify data which should be serialized to JSON.
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     *
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

    /**
     * @return void
     * @throws \Da\Mailer\Exception\UndefinedMessageBrokerException
     */
    public function enqueue()
    {
        $job = MailJobBuilder::make(['message' => json_encode($this)]);
        QueueBuilder::make()->enqueue($job);
    }

    /**
     * @param string $path
     * @param string|null $name
     * @return void
     */
    public function addAttachment(string $path, ?string $name = null): void
    {
        if (is_null($this->attachments)) {
            $this->attachments = [File::make($path, $name)];
            return;
        }

        $this->attachments[] = File::make($path, $name);
    }

    /**
     * @return array
     */
    public function getAttachments(): array
    {
        return $this->attachments ?? [];
    }
}
