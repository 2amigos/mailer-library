<?php
namespace Da\Mailer\Transport;

use Da\Mailer\Helper\ArrayHelper;

class SmtpTransportFactory extends AbstractTransportFactory
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $options)
    {
        parent::__construct($options);
    }

    /**
     * Creates a SmtpTransport.
     *
     * @return SmtpTransport the instance created
     */
    public function create()
    {
        $host = ArrayHelper::remove($this->options, 'host');
        $port = ArrayHelper::remove($this->options, 'port');
        $options = ArrayHelper::getValue($this->options, 'options', []);

        return new SmtpTransport($host, $port, $options);
    }
}
