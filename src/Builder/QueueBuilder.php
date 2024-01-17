<?php

namespace Da\Mailer\Builder;

use Da\Mailer\Enum\MessageBrokerEnum;
use Da\Mailer\Exception\UndefinedMessageBrokerException;
use Da\Mailer\Queue\Backend\Beanstalkd\BeanstalkdQueueStoreAdapter;
use Da\Mailer\Queue\Backend\Beanstalkd\BeanstalkdQueueStoreConnection;
use Da\Mailer\Queue\Backend\Pdo\PdoQueueStoreAdapter;
use Da\Mailer\Queue\Backend\Pdo\PdoQueueStoreConnection;
use Da\Mailer\Queue\Backend\QueueStoreAdapterInterface;
use Da\Mailer\Queue\Backend\RabbitMq\RabbitMqQueueConnection;
use Da\Mailer\Queue\Backend\RabbitMq\RabbitMqQueueStoreAdapter;
use Da\Mailer\Queue\Backend\Redis\RedisQueueStoreAdapter;
use Da\Mailer\Queue\Backend\Redis\RedisQueueStoreConnection;
use Da\Mailer\Queue\Backend\Sqs\SqsQueueStoreAdapter;
use Da\Mailer\Queue\Backend\Sqs\SqsQueueStoreConnection;
use Da\Mailer\Queue\MailQueue;

class QueueBuilder extends Buildable
{
    /**
     * @param string|null $broker
     * @return MailQueue
     * @throws UndefinedMessageBrokerException
     */
    public static function make($broker = null): MailQueue
    {
        $config = self::getConfig();

        $messageBroker = $broker ?? $config['config']['message_broker'];
        $queueAdapter = self::getBrokerAdapter($messageBroker);

        return new MailQueue($queueAdapter);
    }

    /**
     * @var string $broker
     * @return QueueStoreAdapterInterface
     * @throws UndefinedMessageBrokerException
     */
    protected static function getBrokerAdapter($messageBroker)
    {
        $config = self::getConfig();
        $connectionValues = $config['brokers'][$messageBroker];

        switch($messageBroker) {
            case MessageBrokerEnum::BROKER_REDIS:
                return new RedisQueueStoreAdapter(
                    new RedisQueueStoreConnection($connectionValues)
                );
            case MessageBrokerEnum::BROKER_BEANSTALKD:
                return new BeanstalkdQueueStoreAdapter(
                    new BeanstalkdQueueStoreConnection($connectionValues)
                );
            case MessageBrokerEnum::BROKER_PDO:
                return new PdoQueueStoreAdapter(
                    new PdoQueueStoreConnection($connectionValues)
                );
            case MessageBrokerEnum::BROKER_SQS:
                return new SqsQueueStoreAdapter(
                    new SqsQueueStoreConnection($connectionValues)
                );
            case MessageBrokerEnum::BROKER_RABBITMQ:
                return new RabbitMqQueueStoreAdapter(
                    new RabbitMqQueueConnection($connectionValues)
                );
            default: throw new UndefinedMessageBrokerException();
        }
    }
}
