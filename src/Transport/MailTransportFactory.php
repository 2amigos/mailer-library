<?php
namespace Da\Mailer\Transport;

use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\NativeTransportFactory;
use Symfony\Component\Mailer\Transport\TransportInterface;

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
        $dsn = Dsn::fromString($this->options['dns']);

        return (new NativeTransportFactory(null, null, null))->create($dsn);
    }
}
