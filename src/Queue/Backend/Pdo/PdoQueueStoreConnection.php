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
        $this->defineConnectionString();
    }

    protected function defineConnectionString()
    {
        if (! isset($this->configuration['dsn'])) {
            $this->configuration['dsn'] = sprintf("mysql:host=%s;dbname=%s;port=%s", $this->configuration['host'] ?? '', $this->configuration['db'] ?? '', $this->configuration['port'] ?? 3306);
        }
    }

    /**
     * @return PdoQueueStoreConnection
     */
    public function connect()
    {
        $this->disconnect();
        $username = $this->getConfigurationValue('username');
        $password = $this->getConfigurationValue('password');
        $options = $this->getConfigurationValue('options');
        $this->instance = new PDO($this->getConfigurationValue('dsn'), $username, $password, $options);
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
