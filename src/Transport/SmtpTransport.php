<?php
namespace Da\Mailer\Transport;

use Swift_SmtpTransport;

class SmtpTransport implements TransportInterface
{
    /**
     * @var Swift_SmtpTransport
     */
    private $instance;
    /**
     * @var string the mail server host name or ip
     */
    private $host;
    /**
     * @var int the mail server port
     */
    private $port;
    /**
     * @var array the extra options for the Smtp transport -ie username, password, encryption, authMode
     */
    private $options = [];

    /**
     * SmtpTransport constructor.
     *
     * @param string $host the mail server name or ip address
     * @param int $port the mail server port
     * @param array $options the extra options
     */
    public function __construct($host = 'localhost', $port = 25, $options = [])
    {
        $this->host = $host;
        $this->port = $port;
        $this->options = $options;
    }

    /**
     * Returns the Swift_SmtpTransport instance.
     *
     * @return Swift_SmtpTransport
     */
    public function getSwiftTransportInstance()
    {
        if ($this->instance === null) {
            $this->instance = Swift_SmtpTransport::newInstance($this->host, $this->port);
            foreach ($this->options as $option => $value) {
                $this->instance->{'set' . ucfirst($option)}($value);
            }
        }

        return $this->instance;
    }
}
