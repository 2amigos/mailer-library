<?php

namespace Da\Mailer\Queue;

use Da\Mailer\Model\MailMessage;
use Da\Mailer\Queue\Backend\AbstractQueueStoreConnection;
use Da\Mailer\Queue\Backend\MailJobInterface;
use Da\Mailer\Queue\Backend\QueueStoreAdapterInterface;
use Da\Mailer\Security\Cypher;
use Da\Mailer\Security\CypherInterface;

final class MailQueue implements QueueStoreAdapterInterface
{
    /**
     * @var QueueStoreAdapterInterface
     */
    private $adapter;
    /**
     * @var CypherInterface|null
     */
    private $cypher;

    /**
     * @param QueueStoreAdapterInterface $adapter
     */
    public function __construct(QueueStoreAdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @return AbstractQueueStoreConnection
     */
    public function getConnection()
    {
        return $this->adapter->getConnection();
    }

    /**
     * @param CypherInterface $cypher
     */
    public function setCypher(CypherInterface $cypher)
    {
        $this->cypher = $cypher;
    }

    /**
     * @return CypherInterface
     */
    public function getCypher()
    {
        return $this->cypher;
    }

    /**
     * {@inheritdoc}
     */
    public function enqueue(MailJobInterface $mailJob)
    {
        $message = $mailJob->getMessage();
        if (null !== $this->getCypher() && $message instanceof MailMessage) {
            $mailJob->setMessage($this->getCypher()->encodeMailMessage($message));
        }

        return $this->adapter->enqueue($mailJob);
    }

    /**
     * {@inheritdoc}
     */
    public function dequeue()
    {
        $mailJob = $this->adapter->dequeue();

        if (null !== $this->getCypher()) {
            $mailJob->setMessage($this->getCypher()->decodeMailMessage($mailJob->getMessage()));
        }

        return $mailJob;
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        return $this->adapter->init();
    }

    /**
     * {@inheritdoc}
     */
    public function ack(MailJobInterface $mailJob)
    {
        return $this->adapter->ack($mailJob);
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->adapter->isEmpty();
    }
}
