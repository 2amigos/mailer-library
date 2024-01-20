<?php

namespace Da\Mailer\Transport;

use Swift_MailTransport;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\NativeTransportFactory;

class MailTransport implements TransportInterface
{
    /**
     * @var \Symfony\Component\Mailer\Transport\TransportInterface
     */
    private $instance;
    private string $dsn;
    public function __construct(string $dsn)
    {
        $this->dsn = $dsn;
    }

    /**
     * @return \Symfony\Component\Mailer\Transport\TransportInterface
     */
    public function getInstance(): \Symfony\Component\Mailer\Transport\TransportInterface
    {
        if ($this->instance === null) {
            $dsn = Dsn::fromString($this->dsn);
            $this->instance = (new NativeTransportFactory())->create($dsn);
        }

        return $this->instance;
    }
}
