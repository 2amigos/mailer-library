<?php
namespace Da\tests\Queue\Database;

use Da\Mailer\Model\MailMessage;
use Da\Tests\Fixture\FixtureHelper;
use Da\Mailer\Queue\Backend\Pdo\PdoQueueStoreAdapter;
use Da\Tests\AbstractMySqlDatabaseTestCase;

class PdoQueueStoreAdapterTest extends AbstractMySqlDatabaseTestCase
{
    /**
     * @var PdoQueueStoreAdapter
     */
    private $pdoQueueStore;

    protected function setUp()
    {
        parent::setUp();

        $this->pdoQueueStore = new PdoQueueStoreAdapter(self::getPdoQueueStoreConnection());
    }

    public function testEnqueueDequeueAndAcknowledge()
    {
        $mailJob = FixtureHelper::getMailJob();

        $this->assertSame($this->pdoQueueStore, $this->pdoQueueStore->init());

        $this->assertTrue($this->pdoQueueStore->enqueue($mailJob));

        $this->assertTrue($this->pdoQueueStore->isEmpty() === false);

        $mailJob = $this->pdoQueueStore->dequeue();

        $this->assertTrue($this->pdoQueueStore->isEmpty() === true); // message set to 'A' on process

        $this->assertTrue(!empty($mailJob->getMessage()));

        $dequeuedMailMessage = MailMessage::fromArray(json_decode($mailJob->getMessage(), true));

        $this->assertEquals(FixtureHelper::getMailMessage(), $dequeuedMailMessage);

        $mailJob->markAsCompleted();
        $this->pdoQueueStore->ack($mailJob);

        $this->assertTrue($this->pdoQueueStore->dequeue() === null);
    }

    public function testAcknowledgementToUpdateMailJobs()
    {
        $mailJob = FixtureHelper::getMailJob();

        $this->pdoQueueStore->enqueue($mailJob);
        $this->assertTrue($this->pdoQueueStore->isEmpty() === false);
        $dequedMailJob = $this->pdoQueueStore->dequeue();
        $this->assertTrue($this->pdoQueueStore->isEmpty() === true);
        // enqueue it back to be able to get it
        // we could actually set the time to be processed in the future :)
        // lets simply update the increment
        $dequedMailJob->incrementAttempt();
        $dequedMailJob->setTimeToSend(date('Y-m-d H:i:s', time() + 1));
        $this->assertEquals(1, $dequedMailJob->getAttempt());
        $dequedMailJob->markAsNew();
        $this->pdoQueueStore->ack($dequedMailJob);
        sleep(1);
        $this->assertTrue($this->pdoQueueStore->isEmpty() === false);
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testBadMethodCallExceptionOnAck()
    {
        $mailJob = FixtureHelper::getMailJob();
        $this->pdoQueueStore->ack($mailJob);
    }
}
