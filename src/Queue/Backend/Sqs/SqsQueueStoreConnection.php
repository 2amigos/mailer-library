<?php
namespace Da\Mailer\Queue\Backend\Sqs;

use Da\Mailer\Queue\Backend\AbstractQueueStoreConnection;
use Aws\Sqs\SqsClient;

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
     * @inheritdoc
     */
    public function connect()
    {
        if ($this->instance !== null) {
            $this->instance = null; // close previous connection
        }
        $key = $this->getConfigurationValue('key');
        $secret = $this->getConfigurationValue('secret');
        $region = $this->getConfigurationValue('region');

        $this->instance = SqsClient::factory(array(
            'key' => $key,
            'secret' => $secret,
            'region' => $region,
        ));

        return $this;
    }

    /**
     * Returns the connection instance.
     *
     * @return SqsClient|null
     */
    public function getInstance()
    {
        if ($this->instance === null) {
            $this->connect();
        }

        return $this->instance;
    }
}
