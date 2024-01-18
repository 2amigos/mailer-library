<?php

namespace Da\Mailer\Test\Queue\Backend\RabbitMq;

use Da\Mailer\Queue\Backend\RabbitMq\RabbitMqJob;
use Da\Mailer\Queue\Backend\RabbitMq\RabbitMqQueueConnection;
use Da\Mailer\Queue\Backend\RabbitMq\RabbitMqQueueStoreAdapter;
use Da\Mailer\Test\Fixture\FixtureHelper;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use phpseclib3\Crypt\Random;
use PHPUnit\Framework\TestCase;

class RabbitMqQueueAdapterTest extends TestCase
{
    protected $queueStore;
    protected $queueStore2;
    protected $queueStore3;
    protected $mailJob;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mailJob = FixtureHelper::getRabbitMqJob();

        $rabbitMqClient1 = \Mockery::mock(AMQPChannel::class)
            ->makePartial()
            ->shouldReceive([
                'queue_declare' => [null, 0],
                'basic_publish' => '',
            ])
            ->getMock();

        $message = new AMQPMessage(json_encode([
            'id' => $this->mailJob->isNewRecord() ? sha1(Random::string(32)) : $this->mailJob->getId(),
            'attempt' => $this->mailJob->getAttempt(),
            'message' => $this->mailJob->getMessage(),
            'delivery_tag' => null,
        ]));
        $message->setDeliveryTag(1);

        $rabbitMqClient2 = \Mockery::mock(AMQPChannel::class)
            ->makePartial()
            ->shouldReceive([
                'queue_declare' => [null, 2],
                'basic_publish' => [],
                'basic_get' => $message,
                'basic_ack' => null,
                'basic_nack' => null,
            ])
            ->getMock();

        $message2 = new AMQPMessage(json_encode([
            'id' => $this->mailJob->isNewRecord() ? sha1(Random::string(32)) : $this->mailJob->getId(),
            'attempt' => $this->mailJob->getAttempt(),
            'message' => $this->mailJob->getMessage(),
            'delivery_tag' => 1,
        ]));
        $message2->setDeliveryTag(null);

        $rabbitMqClient3 = \Mockery::mock(AMQPChannel::class)
            ->makePartial()
            ->shouldReceive([
                'queue_declare' => [null, 2],
                'basic_publish' => [],
                'basic_get' => $message2,
                'basic_ack' => null,
                'basic_nack' => null,
            ])
            ->getMock();

        /** @var RabbitMqQueueConnection $connection */
        $connection = \Mockery::mock(RabbitMqQueueConnection::class)
            ->shouldReceive('connect')
            ->andReturnSelf()
            ->shouldReceive('getInstance')
            ->andReturn($rabbitMqClient1)
            ->getMock();

        /** @var RabbitMqQueueConnection $connection */
        $connection2 = \Mockery::mock(RabbitMqQueueConnection::class)
            ->shouldReceive('connect')
            ->andReturnSelf()
            ->shouldReceive('getInstance')
            ->andReturn($rabbitMqClient2)
            ->getMock();

        /** @var RabbitMqQueueConnection $connection */
        $connection3 = \Mockery::mock(RabbitMqQueueConnection::class)
            ->shouldReceive('connect')
            ->andReturnSelf()
            ->shouldReceive('getInstance')
            ->andReturn($rabbitMqClient3)
            ->getMock();

        $this->queueStore = new RabbitMqQueueStoreAdapter($connection);
        $this->queueStore2 = new RabbitMqQueueStoreAdapter($connection2);
        $this->queueStore3 = new RabbitMqQueueStoreAdapter($connection3);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        \Mockery::close();
    }

    public function testEnqueueDequeueAndAcknowledgement()
    {
        $this->assertSame($this->queueStore, $this->queueStore->init());

        $this->assertTrue($this->queueStore->isEmpty());
        $this->assertTrue($this->queueStore->enqueue($this->mailJob));

        $this->assertFalse($this->queueStore2->isEmpty());

        $this->assertNull($this->queueStore->dequeue());

        $job = $this->queueStore2->dequeue();
        $this->assertInstanceOf(RabbitMqJob::class, $job);
        $this->queueStore2->ack($job);
        $job->markAsCompleted();

        $this->queueStore2->ack($job);
    }
}
