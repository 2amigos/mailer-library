<?php
namespace Da\Mailer\Transport;

use Da\Helper\ArrayHelper;

class SmtpTransportFactory extends AbstractTransportFactory
{
    public function __construct(array $options)
    {
        parent::__construct($options);
    }

    public function create()
    {
        $host = ArrayHelper::remove($this->options, 'host');
        $port = ArrayHelper::remove($this->options, 'port');
        $options = ArrayHelper::getValue($this->options, 'options', []);

        return new SmtpTransport($host, $port, $options);
    }

}
