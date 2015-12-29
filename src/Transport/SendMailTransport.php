<?php
namespace Da\Mailer\Transport;

use Swift_SendmailTransport;

class SendMailTransport implements TransportInterface
{
    /**
     * @var Swift_SendmailTransport
     */
    private $instance;

    /**
     * @var string the command path to sendmail. Defaults to '/usr/sbin/sendmail -bs'
     */
    private $commandPath;

    /**
     * @param string $commandPath
     */
    public function __construct($commandPath = '/usr/sbin/sendmail -bs')
    {
        $this->commandPath = $commandPath;
    }

    /**
     * Returns the Swift_SendmailTransport instance.
     *
     * @return Swift_SendmailTransport instance
     */
    public function getSwiftTransportInstance()
    {
        if ($this->instance === null) {
            $this->instance = new Swift_SendmailTransport($this->commandPath);
        }

        return $this->instance;
    }
}
