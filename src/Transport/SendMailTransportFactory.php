<?php
namespace Da\Mailer\Transport;

use Symfony\Component\Mailer\Transport\Dsn;

class SendMailTransportFactory extends AbstractTransportFactory
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $options)
    {
        parent::__construct($options);
    }

    /**
     * Returns a SendMailTransport.
     *
     * @return SendMailTransport
     */
    public function create()
    {
        return new SendMailTransport($this->options['dsn']);
    }
}
