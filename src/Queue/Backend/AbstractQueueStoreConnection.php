<?php
namespace Da\Mailer\Queue\Backend;

use Da\Mailer\Helper\ArrayHelper;

abstract class AbstractQueueStoreConnection
{
    /**
     * @var mixed $instance the internal connection instance (ie. PDO)
     */
    protected $instance;
    /**
     * @var array
     */
    protected $configuration = [];

    /**
     * AbstractQueueStoreConnection constructor.
     *
     * @param array $configuration
     */
    protected function __construct(array $configuration = [])
    {
        $this->configuration = $configuration;
    }

    /**
     * @param $key
     * @param null $default
     *
     * @return mixed
     */
    protected function getConfigurationValue($key, $default = null)
    {
        return ArrayHelper::getValue($this->configuration, $key, $default);
    }

    /**
     * @return AbstractQueueStoreConnection
     */
    abstract public function connect();

    /**
     * @return mixed
     */
    abstract public function getInstance();
}
