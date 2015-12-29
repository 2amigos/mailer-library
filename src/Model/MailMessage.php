<?php
namespace Da\Mailer\Model;

use Da\Helper\RecipientsHelper;
use JsonSerializable;
use Swift_Attachment;
use Swift_Message;

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
    public $attachments;

    /**
     * @inheritdoc
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    /**
     * Converts the object into a Swift_Message instance.
     *
     * @return Swift_Message
     */
    public function asSwiftMessage()
    {
        $message = (new Swift_Message());

        if (isset($this->bodyHtml)) {
            $message->setBody($this->bodyHtml, 'text/html');
        }

        if (isset($this->bodyText)) {
            $method = isset($this->bodyHtml) ? 'addPart' : 'setBody';
            $message->$method($this->bodyText, 'text/plain');
        }
        foreach (['from', 'to', 'cc', 'bcc'] as $attribute) {
            if (isset($this->$attribute)) {
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
     * which is a value of any type other than a resource.
     *
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
