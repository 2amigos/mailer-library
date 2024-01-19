<?php
namespace Da\Mailer\Queue\Backend\Sqs;

use Aws\Sqs\SqsClient;
use Da\Mailer\Queue\Backend\AbstractQueueStoreConnection;

class SqsQueueStoreConnection extends AbstractQueueStoreConnection
{
    /**
     * SqsQueueStoreConnection constructor.
     *
     * @param array $configuration
     */
    public function __construct(array $configuration = [])
    {
        parent::__construct($configuration);
    }

    /**
     * @return $this
     */
    public function connect()
    {
        $this->disconnect();
        $key = $this->getConfigurationValue('key');
        $secret = $this->getConfigurationValue('secret');
        $region = $this->getConfigurationValue('region');

        $this->instance = new SqsClient([
            'credentials' => ['key' => $key,
            'secret' => $secret,
            'region' => $region,
        ]);

        return $this;
    }

    /**
     * Returns the connection instance.
     *
     * @return SqsClient
     */
    public function getInstance()
    {
        if ($this->instance === null) {
            $this->connect();
        }

        return $this->instance;
    }
}
