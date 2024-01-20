<?php
namespace Da\Mailer\Test\Queue\Backend\Pdo;

use Da\Mailer\Queue\Backend\Pdo\PdoQueueStoreConnection;
use Da\Mailer\Test\Fixture\FixtureHelper;
use PDO;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class PdoQueueStoreConnectionTest extends TestCase
{
    public function testGetConfigurationValue()
    {
        $class = new ReflectionClass(PdoQueueStoreConnection::class);

        $method = $class->getMethod('getConfigurationValue');
        $method->setAccessible(true);

        $connectionString = 'mysql:host=localhost;dbname=test';
        $username = 'root';
        $password = 'password';
        $options = [
            PDO::ATTR_PERSISTENT => true,
        ];

        $connection = new PdoQueueStoreConnection(
            [
                'connectionString' => $connectionString,
                'username' => $username,
                'password' => $password,
                'options' => $options,
            ]
        );

        $this->assertEquals($connectionString, $method->invoke($connection, 'connectionString'));
        $this->assertEquals($username, $method->invoke($connection, 'username'));
        $this->assertEquals($password, $method->invoke($connection, 'password'));
        $this->assertEquals($options, $method->invoke($connection, 'options'));
    }

    public function testConnect()
    {
        $connection = new PdoQueueStoreConnection(
            FixtureHelper::getMySqlConnectionConfiguration()
        );

        $this->assertSame($connection, $connection->connect());
        $this->assertTrue($connection->getInstance() instanceof PDO);
    }
}
