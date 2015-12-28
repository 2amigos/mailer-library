<?php
namespace Da\Mailer\Transport;

use Swift_SendmailTransport;

/**
 * SendMailTransport.
 */
class SendMailTransport implements TransportInterface
{
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
     * @return \Swift_Transport
     */
    public function getSwiftTransportInstance()
    {
        if ($this->instance === null) {
            $this->instance = Swift_SendmailTransport::newInstance($this->commandPath);
        }

        return $this->instance;
    }
}
