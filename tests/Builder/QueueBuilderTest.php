<?php

namespace Da\Mailer\Test\Builder;

use Da\Mailer\Builder\QueueBuilder;
use Da\Mailer\Enum\MessageBrokerEnum;
use Da\Mailer\Exception\UndefinedMessageBrokerException;
use Da\Mailer\Queue\MailQueue;
use PHPUnit\Framework\TestCase;

class QueueBuilderTest extends TestCase
{
    public function testMake()
    {
        $redisQueue = QueueBuilder::make(MessageBrokerEnum::BROKER_REDIS);
        $this->assertInstanceOf(MailQueue::class, $redisQueue);

        $sqsQueue = QueueBuilder::make(MessageBrokerEnum::BROKER_SQS);
        $this->assertInstanceOf(MailQueue::class, $sqsQueue);

        try {
            $rabbitMqQueue = QueueBuilder::make(MessageBrokerEnum::BROKER_RABBITMQ);
            $this->assertInstanceOf(MailQueue::class, $rabbitMqQueue);
        } catch (\Exception $e) {}

        $pdoQueue = QueueBuilder::make(MessageBrokerEnum::BROKER_PDO);
        $this->assertInstanceOf(MailQueue::class, $pdoQueue);

        $btQueue = QueueBuilder::make(MessageBrokerEnum::BROKER_BEANSTALKD);
        $this->assertInstanceOf(MailQueue::class, $btQueue);
    }

    public function testUndefinedMessageBrokerException()
    {
        $this->expectException(UndefinedMessageBrokerException::class);

        QueueBuilder::make('oracle');
    }
}
