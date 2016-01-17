<?php
namespace Da\Mailer\Queue\Backend\Beanstalk;

use Da\Mailer\Queue\Backend\AbstractQueueStoreConnection;
use Pheanstalk\Pheanstalk;
use Pheanstalk\PheanstalkInterface;

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
        $port = $this->getConfigurationValue('port', PheanstalkInterface::DEFAULT_PORT);
        $connectionTimeout = $this->getConfigurationValue('connectionTimeout');
        $connectPersistent = $this->getConfigurationValue('connectPersistent', false);
        $this->instance = new Pheanstalk($host, $port, $connectionTimeout, $connectPersistent);

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
