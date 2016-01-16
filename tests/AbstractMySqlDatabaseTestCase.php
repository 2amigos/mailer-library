<?php
namespace Da\Mailer\Test;

use Da\Mailer\Queue\Backend\Pdo\PdoQueueStoreConnection;
use Da\Mailer\Test\Fixture\FixtureHelper;
use PHPUnit_Extensions_Database_TestCase;

abstract class AbstractMySqlDatabaseTestCase extends PHPUnit_Extensions_Database_TestCase
{
    protected static function getPdoQueueStoreConnection()
    {
        static $pdoQueue;

        if ($pdoQueue === null) {
            $pdoQueue = new PdoQueueStoreConnection(FixtureHelper::getMySqlConnectionConfiguration());
        }

        return $pdoQueue;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConnection()
    {
        $pdo = self::getPdoQueueStoreConnection();

        return $this->createDefaultDBConnection($pdo->getInstance());
    }

    /**
     * {@inheritdoc}
     */
    protected function getDataSet()
    {
        return $this->createFlatXMLDataSet(__DIR__ . '/data/test.xml');
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $sql = file_get_contents(__DIR__ . '/migrations/mysql.sql');

        $statements = array_map('trim', array_filter(explode(';', $sql)));

        foreach ($statements as $sqlQuery) {
            if (empty($sqlQuery)) {
                continue;
            }
            $query = self::getPdoQueueStoreConnection()->getInstance()->prepare($sqlQuery);
            $query->execute();
        }

        parent::setUp();
    }
}
