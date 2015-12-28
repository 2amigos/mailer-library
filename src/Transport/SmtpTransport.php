<?php
namespace Da\Mailer\Transport;

use Swift_SmtpTransport;

/**
 * SmtpTransport.
 */
class SmtpTransport implements TransportInterface
{
    private $instance;
    /**
     * @var string
     */
    private $host;
    /**
     * @var int
     */
    private $port;
    /**
     * @var array
     */
    private $options = [];

    /**
     * @param string $host
     * @param int $port
     * @param array $transportOptions
     */
    public function __construct($host = 'localhost', $port = 25, $transportOptions = [])
    {
        $this->host = $host;
        $this->port = $port;
        $this->options = $transportOptions;
    }

    /**
     * @return \Swift_Transport
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
