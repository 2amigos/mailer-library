<?php
namespace Da\Mailer\Transport;

class SendMailTransportFactory extends AbstractTransportFactory
{
    /**
     * @inheritdoc
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
        $aCommandPath = isset($this->options['options']) ? $this->options['options'] : '';
        if (empty($aCommandPath) || !is_string($aCommandPath)) {
            $aCommandPath = '/usr/sbin/sendmail -bs';
        }

        return new SendMailTransport($aCommandPath);
    }
}
