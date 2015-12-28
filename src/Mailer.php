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
     * @var Swift_Mailer
     */
    private $swift;
    /**
     * @var TransportInterface|null
     */
    private $transport = null;
    /**
     * @var Swift_Plugins_Loggers_ArrayLogger
     */
    private $logger = null;
    private $logging = true;
    private $dryRun = false;
    private $plugins = [];

    /**
     * Constructor.
     *
     * @param TransportInterface $transport
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
     * If you need to send to each recipient without disclosing details about the
     * other recipients see {@link batchSend()}.
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
     * @param MailMessage $message
     * @param array $views
     * @param array $data
     *
     * @return array|null
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
        $this->mailer = null;

        return $this;
    }

    /**
     * @param Swift_Events_EventListener $plugin
     *
     * @return $this
     */
    public function addPlugin(Swift_Events_EventListener $plugin)
    {
        $this->plugins[] = $plugin;

        return $this;
    }

    /**
     * Registers the plugins.
     */
    public function registerPlugins()
    {
        foreach ($this->plugins as $plugin) {
            if ($plugin instanceof Swift_Events_EventListener) {
                $this->getSwiftMailerInstance()->registerPlugin($plugin);
            }
        }
        $this->logger = new Swift_Plugins_Loggers_ArrayLogger();
        $this->getSwiftMailerInstance()->registerPlugin(new Swift_Plugins_LoggerPlugin($this->logger));

        return $this;
    }

    /**
     * @param MailMessage $mailMessage
     *
     * @return Mailer
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
