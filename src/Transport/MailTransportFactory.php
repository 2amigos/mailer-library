<?php
namespace Da\Mailer\Transport;

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
     * @return MailTransport
     */
    public function create()
    {
        $extraParams = isset($this->options['options']) ? $this->options['options'] : '';
        if (empty($extraParams) || !is_string($extraParams)) {
            $extraParams = '-f%s';
        }

        return new MailTransport($extraParams);
    }
}
