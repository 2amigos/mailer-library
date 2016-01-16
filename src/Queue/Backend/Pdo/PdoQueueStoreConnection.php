<?php
namespace Da\Mailer\Queue\Backend\Pdo;

use Da\Mailer\Queue\Backend\AbstractQueueStoreConnection;
use PDO;

class PdoQueueStoreConnection extends AbstractQueueStoreConnection
{
    /**
     * PdoQueueStoreConnection constructor.
     *
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        parent::__construct($configuration);
    }

    /**
     * @return PdoQueueStoreConnection
     */
    public function connect()
    {
        if ($this->instance !== null) {
            $this->instance = null; // close previous connection
        }
        $connectionString = $this->getConfigurationValue('connectionString');
        $username = $this->getConfigurationValue('username');
        $password = $this->getConfigurationValue('password');
        $options = $this->getConfigurationValue('options');

        $this->instance = new PDO($connectionString, $username, $password, $options);
        $this->instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $this;
    }

    /**
     * Returns the connection instance.
     *
     * @return PDO
     */
    public function getInstance()
    {
        if ($this->instance === null) {
            $this->connect();
        }

        return $this->instance;
    }
}
