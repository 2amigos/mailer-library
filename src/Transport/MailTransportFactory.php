<?php
namespace Da\Mailer\Transport;

/**
 *
 * MailTransportFactory.php
 *
 * Date: 28/12/15
 * Time: 13:12
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 */
class MailTransportFactory extends AbstractTransportFactory
{
    public function __construct(array $options)
    {
        parent::__construct($options);
    }

    public function create()
    {
        $extraParams = isset($this->options['options']) ? $this->options['options'] : '';
        if (empty($extraParams) || !is_string($extraParams)) {
            $extraParams = '-f%s';
        }

        return new MailTransport($extraParams);
    }

}
