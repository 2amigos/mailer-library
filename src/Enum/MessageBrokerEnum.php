<?php

namespace Da\Mailer\Enum;

use MabeEnum\Enum;

class MessageBrokerEnum extends Enum
{
    public const BROKER_REDIS = 'redis';
    public const BROKER_SQS = 'sqs';
    public const BROKER_BEANSTALKD = 'beanstalkd';
    public const BROKER_PDO = 'pdo';
    public const BROKER_RABBITMQ = 'rabbitmq';
}
