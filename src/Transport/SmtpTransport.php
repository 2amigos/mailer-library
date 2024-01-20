<?php
namespace Da\Mailer\Transport;

use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;

class SmtpTransport implements TransportInterface
{
    /**
     * @var EsmtpTransport
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
     * @return EsmtpTransport
     */
    public function getInstance(): EsmtpTransport
    {
        if ($this->instance === null) {
            $user = $this->options['username'] ?? null;
            $password = $this->options['password'] ?? null;

            $this->instance = (new EsmtpTransportFactory())->create(
                new Dsn('smtp', $this->host, $user, $password, $this->port, $this->options)
            );
        }

        return $this->instance;
    }

    private function getScheme()
    {
        return $this->options['tls']
            ? 'smtps'
            : 'smtp';
    }
}
