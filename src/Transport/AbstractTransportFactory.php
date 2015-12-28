<?php
namespace Da\Mailer\Transport;


abstract class AbstractTransportFactory
{
    protected $options;

    protected function __construct(array $options)
    {
        $this->options = $options;
    }

    abstract public function create();
}
