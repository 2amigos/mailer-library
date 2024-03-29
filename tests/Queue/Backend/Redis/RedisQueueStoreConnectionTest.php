<?php
namespace Da\Mailer\Test\Queue\Backend\Redis;

use Da\Mailer\Queue\Backend\Redis\RedisQueueStoreConnection;
use PHPUnit\Framework\TestCase;
use Predis\Client;
use ReflectionClass;

class RedisQueueStoreConnectionTest extends TestCase
{
    public function testGetConfigurationValue()
    {
        $class = new ReflectionClass(RedisQueueStoreConnection::class);
        $method = $class->getMethod('getConfigurationValue');
        $method->setAccessible(true);
        $host = 'localhost';
        $port = '9367';
        $connection = new RedisQueueStoreConnection([
            'host' => $host,
            'port' => $port,
        ]);
        $this->assertEquals($host, $method->invoke($connection, 'host'));
        $this->assertEquals($port, $method->invoke($connection, 'port'));
    }
    public function testConnect()
    {
        $connection = new RedisQueueStoreConnection([
            'host' => 'localhost',
            'port' => '9367',
        ]);
        $this->assertTrue($connection->getInstance() instanceof Client);
        $this->assertSame($connection, $connection->connect());
    }
}
