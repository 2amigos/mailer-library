<?php
namespace Da\Mailer\Test\Queue\Backend\Redis;

use Da\Mailer\Queue\Backend\Beanstalk\BeanstalkdQueueStoreConnection;
use Da\Mailer\Queue\Backend\Redis\RedisQueueStoreConnection;
use Pheanstalk\Pheanstalk;
use PHPUnit_Framework_TestCase;
use Predis\Client;
use ReflectionClass;

class BeanstalkdQueueStoreConnectionTest extends PHPUnit_Framework_TestCase
{
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
        $connection = new BeanstalkdQueueStoreConnection([
            'host' => '127.0.0.1',
        ]);
        $this->assertTrue($connection->getInstance() instanceof Pheanstalk);
        $this->assertSame($connection, $connection->connect());
    }
}
