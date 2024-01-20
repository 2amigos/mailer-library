<?php

namespace Da\Mailer\Transport;

use Symfony\Component\Mailer\Transport\Dsn;

class SendMailTransport implements TransportInterface
{
    /**
     * @var \Symfony\Component\Mailer\Transport\SendmailTransport
     */
    private $instance;
/**
     * @var string
     */
    private string $dsn;
    public function __construct(string $dsn)
    {
        $this->dsn = $dsn;
    }

    /**
     * Returns the Swift_SendmailTransport instance.
     *
     * @return \Symfony\Component\Mailer\Transport\SendmailTransport instance
     */
    public function getInstance(): \Symfony\Component\Mailer\Transport\SendmailTransport
    {
        if ($this->instance === null) {
            $sendMailFactory = new \Symfony\Component\Mailer\Transport\SendmailTransportFactory();
            $this->instance = $sendMailFactory->create(Dsn::fromString($this->dsn));
        }

        return $this->instance;
    }
}
