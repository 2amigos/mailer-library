<?php

namespace Da\Mailer\Test\Builder;

use Da\Mailer\Builder\MailJobBuilder;
use Da\Mailer\Enum\MessageBrokerEnum;
use Da\Mailer\Exception\UndefinedMessageBrokerException;
use Da\Mailer\Queue\Backend\Beanstalkd\BeanstalkdMailJob;
use Da\Mailer\Queue\Backend\Pdo\PdoMailJob;
use Da\Mailer\Queue\Backend\RabbitMq\RabbitMqJob;
use Da\Mailer\Queue\Backend\Redis\RedisMailJob;
use Da\Mailer\Queue\Backend\Sqs\SqsMailJob;
use PHPUnit\Framework\TestCase;

class MailJobBuilderTest extends TestCase
{
    public function testMake()
    {
        $redis = MailJobBuilder::make([],MessageBrokerEnum::BROKER_REDIS);
        $this->assertInstanceOf(RedisMailJob::class, $redis);

        $sqs = MailJobBuilder::make([],MessageBrokerEnum::BROKER_SQS);
        $this->assertInstanceOf(SqsMailJob::class, $sqs);

        $bTalked = MailJobBuilder::make([],MessageBrokerEnum::BROKER_BEANSTALKD);
        $this->assertInstanceOf(BeanstalkdMailJob::class, $bTalked);

        $pdo = MailJobBuilder::make([],MessageBrokerEnum::BROKER_PDO);
        $this->assertInstanceOf(PdoMailJob::class, $pdo);

        $rabbitMq = MailJobBuilder::make([],MessageBrokerEnum::BROKER_RABBITMQ);
        $this->assertInstanceOf(RabbitMqJob::class, $rabbitMq);

        // test using .env.testing file config
        $default = MailJobBuilder::make([]);
        $this->assertInstanceOf(RedisMailJob::class, $default);
    }

    public function testUndefinedBrokerException()
    {
        $this->expectException(UndefinedMessageBrokerException::class);

        MailJobBuilder::make([], 'oracle');
    }
}
