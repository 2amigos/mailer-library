<?php
namespace Da\Mailer;

use Da\Helper\ArrayHelper;
use Da\Helper\PhpViewFileHelper;
use Da\Mailer\Model\MailMessage;
use Da\Mailer\Transport\TransportFactory;
use Da\Mailer\Transport\TransportInterface;
use Swift_Events_EventListener;
use Swift_Mailer;
use Swift_Message;
use Swift_Plugins_LoggerPlugin;
use Swift_Plugins_Loggers_ArrayLogger;

class Mailer
{
    /**
     * @var Swift_Mailer the instance.
     */
    private $swift;
    /**
     * @var TransportInterface|null the transport used to send emails.
     */
    private $transport = null;
    /**
     * @var Swift_Plugins_Loggers_ArrayLogger the plugging used to
     */
    private $logger = null;
    /**
     * @var bool whether we enable logging or not. The `$logger` plugin won't be added to the Swift_Mailer instance.
     */
    private $logging = true;
    /**
     * @var bool whether we are testing sendings. If true, emails won't be sent.
     */
    private $dryRun = false;
    /**
     * @var array a list of Swift_Mailer plugins to be registered.
     */
    private $plugins = [];

    /**
     * Constructor.
     *
     * @param TransportInterface $transport the transport to use for sending emails.
     * @param bool $dryRun
     * @param bool $doLogging
     */
    public function __construct(TransportInterface $transport, $dryRun = false, $doLogging = true)
    {
        $this->transport = $transport;
        $this->dryRun = $dryRun;
        $this->logging = $doLogging;
    }

    /**
     * Returns the mail transport used.
     *
     * @return null|TransportInterface
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * Returns the swift mailer.
     *
     * @return null|\Swift_Mailer
     */
    public function getSwiftMailerInstance()
    {
        if ($this->swift === null) {
            $swiftTransport = $this->getTransport()->getSwiftTransportInstance();
            $this->swift = new Swift_Mailer($swiftTransport);
        }

        return $this->swift;
    }

    /**
     * @return null|string logged messages as string
     */
    public function getLog()
    {
        return $this->logging && $this->logger !== null ? $this->logger->dump() : null;
    }

    /**
     * Modifies the transport used.
     *
     * @param TransportInterface $transport
     */
    public function updateTransport(TransportInterface $transport)
    {
        $this->transport = $transport;
        $this->resetSwiftMailer();
    }

    /**
     * Sends a Swift_Mime_Message as it would be sent in an mail client.
     *
     * All recipients (with the exception of Bcc) will be able to see the other recipients this message was sent to.
     *
     * Recipient/sender data will be retrieved from the {Swift_Mime_Message} object.
     *
     * The return value is the number of recipients who were accepted for
     * delivery.
     *
     * @param Swift_Message $message
     *
     * @return array|null
     */
    public function sendSwiftMessage(Swift_Message $message)
    {
        if ($this->dryRun) {
            return count($message->getTo());
        }

        $failedRecipients = null;

        $this->getSwiftMailerInstance()->send($message, $failedRecipients);

        return $failedRecipients;
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
     * @return array|null
     *
     * @see PhpViewFileHelper::render()
     * @see MailMessage::$bodyHtml
     * @see MailMessage::$bodyText
     */
    public function send(MailMessage $message, array $views = [], array $data = [])
    {
        foreach (['text', 'html'] as $view) {
            $viewFile = ArrayHelper::getValue($views, $view);
            if ($viewFile !== null) {
                $content = PhpViewFileHelper::render($viewFile, $data);
                $attribute = 'body' . ucfirst($view);
                $message->$attribute = $content;
            }
        }

        return $this->sendSwiftMessage($message->asSwiftMessage());
    }

    /**
     * Resets the swift mailer back to null.
     */
    public function resetSwiftMailer()
    {
        $this->swift = null;

        return $this;
    }

    /**
     * Adds a Swift_Mailer plugin to the stack so it can be later registered with `registerPlugins()`
     *
     * @param Swift_Events_EventListener $plugin
     *
     * @return $this
     *
     * @link http://swiftmailer.org/docs/plugins.html
     */
    public function addPlugin(Swift_Events_EventListener $plugin)
    {
        $this->plugins[] = $plugin;

        return $this;
    }

    /**
     * Registers the plugins to the Swift_Mailer instance.
     *
     * @link http://swiftmailer.org/docs/plugins.html
     */
    public function registerPlugins()
    {
        foreach ($this->plugins as $plugin) {
            if ($plugin instanceof Swift_Events_EventListener) {
                $this->getSwiftMailerInstance()->registerPlugin($plugin);
            }
        }
        if ($this->logging === true) {
            $this->logger = new Swift_Plugins_Loggers_ArrayLogger();
            $this->getSwiftMailerInstance()->registerPlugin(new Swift_Plugins_LoggerPlugin($this->logger));
        }

        return $this;
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
