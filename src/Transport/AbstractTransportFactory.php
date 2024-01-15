<?php
namespace Da\Mailer\Transport;

use Symfony\Component\Mailer\Transport\TransportInterface;

abstract class AbstractTransportFactory
{
    /**
     * @var array the options to configure the transport instance to create.
     */
    protected $options;

    /**
     * AbstractTransportFactory constructor.
     *
     * @param array $options
     */
    protected function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * @return TransportInterface
     */
    abstract public function create();
}
