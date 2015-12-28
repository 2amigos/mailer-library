<?php
namespace Da\Mailer\Queue\Backend;

/**
 *
 * QueueStoreAdapterInterface.php
 *
 * Date: 24/12/15
 * Time: 13:06
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 */
interface QueueStoreAdapterInterface
{
    /**
     * @return QueueStoreAdapterInterface
     */
    public function init();

    /**
     * @param MailJobInterface $mailJob
     *
     * @return bool
     */
    public function enqueue(MailJobInterface $mailJob);

    /**
     * @return MailJobInterface
     */
    public function dequeue();

    /**
     * @param MailJobInterface $mailJob
     */
    public function ack(MailJobInterface $mailJob);

    /**
     * @return bool
     */
    public function isEmpty();
}
