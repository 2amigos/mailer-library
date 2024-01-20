<?php

namespace Da\Mailer\Queue\Backend\Beanstalkd;

use Da\Mailer\Queue\Backend\AbstractQueueStoreConnection;
use Pheanstalk\Connection;
use Pheanstalk\Pheanstalk;
use Pheanstalk\SocketFactory;

class BeanstalkdQueueStoreConnection extends AbstractQueueStoreConnection
{
    /**
     * BeanstalkdQueueStoreConnection constructor.
     *
     * @param array $configuration
     *
     * @see connect for options
     */
    public function __construct(array $configuration)
    {
        parent::__construct($configuration);
    }

    /**
     * @return BeanstalkdQueueStoreConnection
     */
    public function connect()
    {
        $this->disconnect();
        $host = $this->getConfigurationValue('host', '127.0.0.1');
        $port = $this->getConfigurationValue('port', Pheanstalk::DEFAULT_PORT);
        $connectionTimeout = $this->getConfigurationValue('connectionTimeout');
        $connectPersistent = $this->getConfigurationValue('connectPersistent', false);
        $connection = new Connection(new SocketFactory($host, $port ?: Pheanstalk::DEFAULT_PORT, $connectionTimeout ?? 0, $connectPersistent ?? SocketFactory::AUTODETECT));
        $this->instance = new Pheanstalk($connection);
        return $this;
    }

    /**
     * @return Pheanstalk
     */
    public function getInstance()
    {
        if ($this->instance === null) {
            $this->connect();
        }

        return $this->instance;
    }
}
