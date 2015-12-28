<?php
namespace Da\Mailer\Queue\Backend\Pdo;

use Da\Mailer\Queue\Backend\AbstractQueueStoreConnection;
use PDO;

/**
 *
 * PdoConnection.php
 *
 * Date: 24/12/15
 * Time: 13:28
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 */
class PdoQueueStoreConnection extends AbstractQueueStoreConnection
{
    public function __construct(array $configuration = [])
    {
        parent::__construct($configuration);
    }

    /**
     * @inheritdoc
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
     * @return PDO|null
     */
    public function getInstance()
    {
        if ($this->instance === null) {
            $this->connect();
        }

        return $this->instance;
    }
}
