<?php

namespace Da\Mailer\Builder;

use Da\Mailer\Enum\MessageBrokerEnum;
use Da\Mailer\Exception\UndefinedMessageBrokerException;
use Da\Mailer\Model\MailJob;
use Da\Mailer\Queue\Backend\Beanstalkd\BeanstalkdMailJob;
use Da\Mailer\Queue\Backend\Pdo\PdoMailJob;
use Da\Mailer\Queue\Backend\RabbitMq\RabbitMqJob;
use Da\Mailer\Queue\Backend\Redis\RedisMailJob;
use Da\Mailer\Queue\Backend\Sqs\SqsMailJob;

class MailJobBuilder extends Buildable
{
    /**
     * @param array|null $jobAttributes
     * @param string|null $broker
     * @return MailJob
     * @throws UndefinedMessageBrokerException
     */
    public static function make($jobAttributes = null, ?string $broker = null): MailJob
    {
        $config = self::getConfig();
        $messageBroker = $broker ?? $config['config']['message_broker'];

        switch ($messageBroker) {
            case MessageBrokerEnum::BROKER_REDIS:
                return new RedisMailJob($jobAttributes);
            case MessageBrokerEnum::BROKER_SQS:
                return new SqsMailJob($jobAttributes);
            case MessageBrokerEnum::BROKER_BEANSTALKD:
                return new BeanstalkdMailJob($jobAttributes);
            case MessageBrokerEnum::BROKER_PDO:
                return new PdoMailJob($jobAttributes);
            case MessageBrokerEnum::BROKER_RABBITMQ:
                return new RabbitMqJob($jobAttributes);
            default:
                throw new UndefinedMessageBrokerException();
        }
    }
}
