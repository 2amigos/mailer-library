<?php

namespace Da\Mailer\Queue\Backend\RabbitMq;

use Da\Mailer\Queue\Backend\MailJobInterface;
use Da\Mailer\Queue\Backend\QueueStoreAdapterInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use phpseclib3\Crypt\Random;

class RabbitMqQueueStoreAdapter implements QueueStoreAdapterInterface
{
    /**
     * @var string
     */
    private $queueName;

    /**
     * @var int
     */
    private $expireTime;

    /**
     * @var RabbitMqQueueConnection
     */
    protected $connection;

    /**
     * @param RabbitMqQueueConnection $connection
     * @param $queueName
     * @param $expireTime
     */
    public function __construct(RabbitMqQueueConnection $connection, $queueName = 'mail_queue', $expireTime = 60)
    {
        $this->connection = $connection;
        $this->expireTime = $expireTime;
        $this->queueName = $queueName;

        $this->init();
    }

    /**
     * @return
     */
    public function init()
    {
        $this->getConnection()
            ->connect();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @var RabbitMqJob|MailJobInterface $mailJob
     *
     * @return bool;
     */
    public function enqueue(MailJobInterface $mailJob)
    {
        /** @var AMQPChannel $chanel */
        $chanel = $this->getConnection()->getInstance();
        $chanel->queue_declare($this->queueName, false, false, false, false);
        $message = new AMQPMessage($this->createPayload($mailJob));
        $chanel->basic_publish($message, '', $this->queueName);

        return true;
    }

    /**
     * @inheritDoc
     * @return RabbitMqJob|MailJobInterface|null
     */
    public function dequeue()
    {
        if ($this->isEmpty()) {
            return null;
        }

        /** @var AMQPChannel $chanel */
        $chanel = $this->getConnection()->getInstance();

        /** @var AMQPMessage $message */
        $message = $chanel->basic_get($this->queueName);

        $data = json_decode($message->getBody(), true);

        return new RabbitMqJob([
            'id' => $data['id'],
            'message' => $data['message'],
            'attempt' => $data['attempt'],
            'deliveryTag' => $message->delivery_info['delivery_tag'],
        ]);
    }

    /**
     * @param RabbitMqJob $mailJob
     */
    public function ack(MailJobInterface $mailJob)
    {
        /** @var AMQPChannel $chanel */
        $chanel = $this->getConnection()->getInstance();
        if ($mailJob->isCompleted()) {
            $chanel->basic_ack($mailJob->getDeliveryTag(), false);
            return;
        }

        $chanel->basic_nack($mailJob->getDeliveryTag(), false, true);
    }

    /**
     * @inheritDoc
     */
    public function isEmpty()
    {
        /** @var AMQPChannel $chanel */
        $chanel = $this->getConnection()->getInstance();
        $queueProperties = $chanel->queue_declare($this->queueName, false, false, false, false);

        return is_array($queueProperties) && $queueProperties[1] === 0;
    }

    /**
     * @param MailJobInterface $mailJob
     * @return false|string
     */
    protected function createPayload(MailJobInterface $mailJob)
    {
        return json_encode([
            'id' => $mailJob->isNewRecord() ? sha1(Random::string(32)) : $mailJob->getId(),
            'attempt' => $mailJob->getAttempt(),
            'message' => $mailJob->getMessage(),
            'delivery_tag' => null,
        ]);
    }
}
