<?php
namespace Da\Mailer\Transport;

use Da\Mailer\Transport\TransportInterface;

class MailTransportFactory extends AbstractTransportFactory
{
    /**
     * MailTransportFactory constructor.
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        parent::__construct($options);
    }

    /**
     * Creates a MailTransport instance.
     *
     * @return TransportInterface
     */
    public function create()
    {
        return new MailTransport($this->options['dsn']);
    }
}
