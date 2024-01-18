<?php
namespace Da\Mailer\Test\Queue\Backend\Beanstalkd;

use Da\Mailer\Queue\Backend\Beanstalkd\BeanstalkdQueueStoreConnection;
use Pheanstalk\Connection;
use Pheanstalk\Pheanstalk;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class BeanstalkdQueueStoreConnectionTest extends TestCase
{
    public function tearDown(): void
    {
        parent::tearDown();

        \Mockery::close();
    }

    public function testGetConfigurationValue()
    {
        $class = new ReflectionClass(BeanstalkdQueueStoreConnection::class);
        $method = $class->getMethod('getConfigurationValue');
        $method->setAccessible(true);
        $host = 'localhost';
        $port = 11300;
        $connection = new BeanstalkdQueueStoreConnection([
            'host' => $host,
            'port' => 11300,
        ]);
        $this->assertEquals($host, $method->invoke($connection, 'host'));
        $this->assertEquals($port, $method->invoke($connection, 'port'));
    }

    public function testConnect()
    {
        $client = \Mockery::mock('\Pheanstalk\Pheanstalk');

        $connection = \Mockery::mock('\Da\Mailer\Queue\Backend\Beanstalkd\BeanstalkdQueueStoreConnection')
            ->shouldReceive('connect')
            ->andReturnSelf()
            ->shouldReceive('getInstance')
            ->andReturn($client)
            ->getMock();

        $this->assertSame($connection, $connection->connect());
    }

    public function testConnectInstance()
    {
        $connection = (new BeanstalkdQueueStoreConnection([]));

        $this->assertInstanceOf(Pheanstalk::class, $connection->getInstance());
        $this->assertInstanceOf(Pheanstalk::class, $connection->getInstance());
    }
}
