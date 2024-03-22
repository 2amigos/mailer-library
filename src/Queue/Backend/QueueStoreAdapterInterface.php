<?php

namespace Da\Mailer\Queue\Backend;

interface QueueStoreAdapterInterface
{
    /**
     * @return QueueStoreAdapterInterface
     */
    public function init();
/**
     * @return AbstractQueueStoreConnection
     */
    public function getConnection();
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

//    public function removeFailedJobs();
}
