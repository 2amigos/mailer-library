<?php
namespace Da\Mailer\Transport;

use Swift_Transport;

interface TransportInterface
{
    const TYPE_MAIL = 'mail';
    const TYPE_SEND_MAIL = 'sendMail';
    const TYPE_SMTP = 'smtp';

    /**
     * Returns the Swift_Transport instance.
     *
     * @return Swift_Transport
     */
    public function getSwiftTransportInstance();
}
