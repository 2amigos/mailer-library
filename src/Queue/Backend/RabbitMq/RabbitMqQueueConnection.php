<?php

namespace Da\Mailer\Queue\Backend\RabbitMq;

use Da\Mailer\Queue\Backend\AbstractQueueStoreConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitMqQueueConnection extends AbstractQueueStoreConnection
{
    /** @var AMQPStreamConnection|null */
    private $connection = null;

    /**
     * RabbitMqQueueConnection constructor
     *
     * @param array $configuration
     *
     * refer to https://php-amqplib.github.io/php-amqplib/classes/PhpAmqpLib-Connection-AMQPStreamConnection.html#method___construct
     * for full list of configuration values
     */
    public function __construct(array $configuration = [])
    {
        parent::__construct($configuration);
    }

    /**
     * @inheritDoc
     */
    public function connect()
    {
        if (! is_null($this->connection)) {
            $this->disconnect();
        }

        $this->connection = new AMQPStreamConnection(
            $this->configuration['host'],
            $this->configuration['port'],
            $this->configuration['user'],
            $this->configuration['password']

        );

        $this->instance = $this->connection->channel();
        $this->instance->confirm_select();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getInstance()
    {
        if (is_null($this->instance)) {
            $this->connect();
        }

        return $this->instance;
    }

    public function disconnect()
    {
        if (is_null($this->connection)) {
            return;
        }

        $this->instance->close();
        $this->connection->close();
        $this->instance = null;
        $this->connection = null;
    }
}
