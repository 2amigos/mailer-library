<?php
namespace Da\Mailer\Queue\Backend;

use Da\Mailer\Model\MailMessage;

/**
 *
 * MailJobInterface.php
 *
 * Date: 25/12/15
 * Time: 10:42
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 */
interface MailJobInterface
{
    /**
     * @param MailMessage|string $message
     */
    public function setMessage($message);

    /**
     * @return MailMessage|string
     */
    public function getMessage();
}
