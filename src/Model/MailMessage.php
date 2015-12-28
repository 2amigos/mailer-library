<?php

namespace Da\Mailer\Model;

use Da\Helper\RecipientsHelper;
use JsonSerializable;
use Swift_Attachment;
use Swift_Message;

/**
 * MailMessage.
 */
class MailMessage extends AbstractMailObject implements JsonSerializable
{
    public $transportOptions = [];
    public $transportType;
    public $host;
    public $port;
    public $from;
    public $to;
    public $cc;
    public $bcc;
    public $subject;
    public $bodyHtml;
    public $bodyText;
    public $attachments;

    public function __construct(array $config = []) {
        parent::__construct($config);
    }

    /**
     * @return Swift_Message
     */
    public function asSwiftMessage()
    {
        $message = (new Swift_Message());

        if(isset($this->bodyHtml)) {
            $message->setBody($this->bodyHtml, 'text/html');
        }

        if(isset($this->bodyText)) {
            $method = isset($this->bodyHtml) ? 'addPart' : 'setBody';
            $message->$method($this->bodyText, 'text/plain');
        }
        foreach(['from', 'to', 'cc', 'bcc'] as $attribute) {
            if(isset($this->$attribute)) {
                $method = 'set' . ucfirst($attribute);
                $message->$method(RecipientsHelper::sanitize($this->$attribute));
            }
        }

        $message->setSubject($this->subject);

        if (is_array($this->attachments)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            foreach ($this->attachments as $filePath) {
                $attachment = Swift_Attachment::fromPath($filePath);
                $mime = finfo_file($finfo, $filePath);
                if ($mime !== false) {
                    $attachment->setContentType($mime);
                }
                $message->attach($attachment);
            }
            finfo_close($finfo);
        }

        return $message;
    }

    /**
     * Specify data which should be serialized to JSON.
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed data which can be serialized by <b>json_encode</b>,
     *               which is a value of any type other than a resource.
     *
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
