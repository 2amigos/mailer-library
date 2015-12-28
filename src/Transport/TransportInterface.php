<?php
namespace Da\Mailer\Transport;

/**
 *
 * TransportInterface.php
 *
 * Date: 26/12/15
 * Time: 22:39
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 */
interface TransportInterface
{
    const TYPE_MAIL = 'mail';
    const TYPE_SEND_MAIL = 'sendMail';
    const TYPE_SMTP = 'smtp';

    /**
     * @return \Swift_Transport
     */
    public function getSwiftTransportInstance();
}
