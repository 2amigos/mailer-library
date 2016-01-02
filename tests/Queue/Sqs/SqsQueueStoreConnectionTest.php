<?php
namespace Da\Mailer\Test\Queue\Sqs;

use Da\Mailer\Queue\Backend\Sqs\SqsQueueStoreConnection;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use Aws\Sqs\SqsClient;

class PdoQueueStoreConnectionTest extends PHPUnit_Framework_TestCase
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

        $this->assertSame($connection, $connection->connect());
        $this->assertTrue($connection->getInstance() instanceof SqsClient);
    }
}
