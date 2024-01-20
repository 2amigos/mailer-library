<?php
namespace Da\Mailer\Test\Queue\Backend\Sqs;

use Aws\Sqs\SqsClient;
use Da\Mailer\Queue\Backend\Sqs\SqsQueueStoreConnection;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class SqsQueueStoreConnectionTest extends TestCase
{
    public function testGetConfigurationValue()
    {
        $class = new ReflectionClass(SqsQueueStoreConnection::class);

        $method = $class->getMethod('getConfigurationValue');
        $method->setAccessible(true);

        $key = 'AKIAxxxxxxxxxxxxxxxZ';
        $secret = 'AxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxZ';
        $region = 'eu-north-99';

        $connection = new SqsQueueStoreConnection([
            'key' => $key,
            'secret' => $secret,
            'region' => $region,
        ]);

        $this->assertEquals($key, $method->invoke($connection, 'key'));
        $this->assertEquals($secret, $method->invoke($connection, 'secret'));
        $this->assertEquals($region, $method->invoke($connection, 'region'));
    }

    public function testConnect()
    {
        $connection = new SqsQueueStoreConnection([
            'key' => 'AKIAxxxxxxxxxxxxxxxZ',
            'secret' => 'AxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxZ',
            'region' => 'eu-north-99',
        ]);

        $this->assertTrue($connection->getInstance() instanceof SqsClient);
        $this->assertSame($connection, $connection->connect());
        $this->assertTrue($connection->getInstance() instanceof SqsClient);
        $this->assertSame($connection, $connection->connect());
        $this->assertTrue($connection->getInstance() instanceof SqsClient);
    }
}
