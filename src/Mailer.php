<?php
namespace Da\Mailer;

use Da\Mailer\Builder\MessageBuilder;
use Da\Mailer\Helper\PhpViewFileHelper;
use Da\Mailer\Model\MailMessage;
use Da\Mailer\Transport\TransportFactory;
use Da\Mailer\Transport\TransportInterface;
use Symfony\Component\Mailer\SentMessage;

class Mailer
{
    /**
     * @var TransportInterface|null the transport used to send emails.
     */
    private $transport = null;
    /**
     * @var bool
     */
    private $logging = true;

    /**
     * Constructor.
     *
     * @param TransportInterface $transport the transport to use for sending emails.
     * @param bool $logging
     */
    public function __construct(TransportInterface $transport, $logging = true)
    {
        $this->transport = $transport;
        $this->logging = $logging;
    }

    /**
     * Returns the mail transport used.
     *
     * @return null|TransportInterface
     */
    public function getTransport(): TransportInterface
    {
        return $this->transport;
    }

    /**
     * Returns the Symfony Mailer Transport instance.
     *
     * @return null|\Symfony\Component\Mailer\Transport\TransportInterface
     */
    public function getTransportInstance()
    {
        return $this->getTransport()->getInstance();
    }

    /**
     * @return null|string logged messages as string
     */
    public function getLog()
    {
        // TODO Add log mechanism
        return null;
    }

    /**
     * Modifies the transport used.
     *
     * @param TransportInterface $transport
     */
    public function setTransport(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * Sends a MailMessage instance.
     *
     * View files can be added to the `$views` array argument and if set, they will be parsed via the `PhpViewFileHelper`
     * helper that this library contains.
     *
     * The `$view` argument has the following syntax:
     *
     * ```
     * $view = [
     *   'text' => '/path/to/plain/text/email.php',
     *   'html' => '/path/to/html/email.php'
     * ];
     * ```
     * The `PhpViewFileHelper` will use the `$data` array argument to parse the templates.
     *
     * The template files must be of `php` type if you wish to use internal system. Otherwise, is highly recommended to
     * use your own template parser and set the `bodyHtml` and `bodyText` of the `MailMessage` class.
     *
     * @param MailMessage $message the MailMessage instance to send
     * @param array $views the view files for `text` and `html` templates
     * @param array $data the data to be used for parsing the templates
     *
     * @return SentMessage|null
     *
     * @see PhpViewFileHelper::render()
     * @see MailMessage::$bodyHtml
     * @see MailMessage::$bodyText
     */
    public function send(MailMessage $message, array $views = [], array $data = []): ?SentMessage
    {
        $message = MessageBuilder::make($message);

        return $this->getTransportInstance()->send($message);
    }

    /**
     * Factory method to create an instance of the mailer based on the configuration of a `MailMessage` instance.
     *
     * @param MailMessage $mailMessage the instance to create the Mailer from
     *
     * @return Mailer instance
     */
    public static function fromMailMessage(MailMessage $mailMessage)
    {
        $options = [
            'host' => $mailMessage->host,
            'port' => $mailMessage->port,
            'options' => $mailMessage->transportOptions,
        ];

        $factory = TransportFactory::create($options, $mailMessage->transportType);

        return new Mailer($factory->create());
    }
}
