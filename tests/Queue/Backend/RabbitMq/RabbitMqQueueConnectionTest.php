<?php

namespace Da\Mailer\Test\Queue\Backend\RabbitMq;

use Da\Mailer\Queue\Backend\RabbitMq\RabbitMqQueueConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use PHPUnit\Framework\TestCase;

class RabbitMqQueueConnectionTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        \Mockery::close();
    }

    public function testConnection()
    {
        $connection = \Mockery::mock(RabbitMqQueueConnection::class)
            ->shouldReceive('connect')
            ->andReturnSelf()
            ->shouldReceive([
                'getInstance' => \Mockery::mock(AMQPChannel::class),
                'disconnect'
            ])
        ->getMock();

        $this->assertInstanceOf(RabbitMqQueueConnection::class, $connection);
        $this->assertSame($connection, $connection->connect());
        $this->assertInstanceOf(AMQPChannel::class, $connection->getInstance());
    }
}
