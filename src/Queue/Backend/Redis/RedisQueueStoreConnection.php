<?php

namespace Da\Mailer\Queue\Backend\Redis;

use Da\Mailer\Queue\Backend\AbstractQueueStoreConnection;
use Predis\Client;

class RedisQueueStoreConnection extends AbstractQueueStoreConnection
{
    /**
     * RedisQueueStoreConnection constructor.
     *
     * @param array $configuration
     *
     * @see https://github.com/nrk/predis/wiki/Connection-Parameters#list-of-connection-parameters for a full list
     * of connection parameters
     */
    public function __construct(array $configuration)
    {
        parent::__construct($configuration);
    }

    /**
     * @return RedisQueueStoreConnection
     */
    public function connect()
    {
        $this->disconnect();
        $this->instance = new Client($this->configuration);
        return $this;
    }

    /**
     * Returns the client predis instance.
     *
     * @return Client
     */
    public function getInstance()
    {
        if ($this->instance === null) {
            $this->connect();
        }

        return $this->instance;
    }
}
