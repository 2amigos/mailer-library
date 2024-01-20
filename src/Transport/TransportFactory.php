<?php

namespace Da\Mailer\Transport;

use Da\Mailer\Enum\TransportType;
use Da\Mailer\Exception\InvalidTransportTypeArgumentException;

class TransportFactory
{
    /**
     * Creates one of the transport supported according to the type passed.
     *
     * @param array $options the options to configure the transport
     * @param string $type the type of transport
     *
     * @return MailTransportFactory|SendMailTransportFactory|SmtpTransportFactory
     */
    public static function create(array $options, $type)
    {
        switch ($type) {
            case TransportType::SEND_MAIL:
                return new SendMailTransportFactory($options);
            case TransportType::MAIL:
                return new MailTransportFactory($options);
            case TransportType::SMTP:
                return new SmtpTransportFactory($options);
            default:
                throw new InvalidTransportTypeArgumentException("Unknown TransportType: '{$type}'");
        }
    }
}
