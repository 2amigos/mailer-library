<?php
namespace Da\tests\Queue\Database;

use Da\Mailer\Test\Fixture\FixtureHelper;
use Da\Mailer\Model\MailMessage;
use Da\Mailer\Queue\Backend\Sqs\SqsQueueStoreAdapter;
use Da\Mailer\Queue\Backend\Sqs\SqsQueueStoreConnection;
use Aws\Sqs\SqsClient;
use Guzzle\Common\Collection;
use PHPUnit_Framework_TestCase;
use Mockery;

class SqsQueueStoreAdapterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqsQueueStoreAdapter
     */
    private $sqsQueueStore;

    protected function setUp()
    {
        parent::setUp();

        $createQueueResult = new Collection([
            'MessageId' => 'createQueueResultId',
            'QueueUrl' => 'http://queue.url/path/',
        ]);

        $sendMessageResult = new Collection([
            'MessageId' => 'sendMessageResultId',
        ]);

        $getQueueAttributesResult1 = new Collection([
            'MessageId' => 'getQueueAttributesResult1Id',
            'Attributes' => [
                'ApproximateNumberOfMessages' => 1,
            ],
        ]);
        $getQueueAttributesResult2 = new Collection([
            'MessageId' => 'getQueueAttributesResult2Id',
            'Attributes' => [
                'ApproximateNumberOfMessages' => 0,
            ],
        ]);

        $receiveMessageResult1 = new Collection([
            'Messages' => [
                [
                    'MessageId' => 'receiveMessageResult1Id',
                    'ReceiptHandle' => 'receiveMessageResult1Handle',
                    'Body' => json_encode(FixtureHelper::getMailMessage()),
                ],
            ],
        ]);
        $receiveMessageResult2 = new Collection([
            // no message(s) returned by Amazon SQS
        ]);

        /** @var SqsClient $sqsClient */
        $sqsClient = Mockery::mock('\Aws\Sqs\SqsClient')
            ->shouldReceive('createQueue')
                ->with(Mockery::mustBe([
                    'QueueName' => 'testing_queue',
                ]))
                ->andReturn($createQueueResult)
            ->shouldReceive('sendMessage')
                ->with(Mockery::mustBe([
                    'QueueUrl' => 'http://queue.url/path/',
                    'MessageBody' => json_encode(FixtureHelper::getMailMessage()),
                    'DelaySeconds' => null,
                ]))
                ->andReturn($sendMessageResult)
            ->shouldReceive('getQueueAttributes')
                ->with(Mockery::mustBe([
                    'QueueUrl' => 'http://queue.url/path/',
                    'AttributeNames' => ['ApproximateNumberOfMessages'],
                ]))
                ->andReturn($getQueueAttributesResult1, $getQueueAttributesResult2)
            ->shouldReceive('receiveMessage')
                ->with(Mockery::mustBe([
                    'QueueUrl' => 'http://queue.url/path/',
                ]))
                ->andReturn($receiveMessageResult1, $receiveMessageResult2)
            ->shouldReceive('deleteMessage')
                ->with(Mockery::mustBe([
                    'QueueUrl' => 'http://queue.url/path/',
                    'ReceiptHandle' => 'receiveMessageResult1Handle',
                ]))
            ->getMock();

        /** @var SqsQueueStoreConnection $sqsQueueStoreConnection */
        $sqsQueueStoreConnection = Mockery::mock('\Da\Mailer\Queue\Backend\Sqs\SqsQueueStoreConnection')
            ->shouldReceive('connect')
                ->andReturnSelf()
            ->shouldReceive('getInstance')
                ->andReturn($sqsClient)
            ->getMock();

        $this->sqsQueueStore = new SqsQueueStoreAdapter($sqsQueueStoreConnection, 'testing_queue');
    }

    public function tearDown()
    {
        parent::tearDown();

        Mockery::close();
    }

    public function testEnqueueDequeueAndAcknowledge()
    {
        $mailJob = FixtureHelper::getSqsMailJob();

        $this->assertSame($this->sqsQueueStore, $this->sqsQueueStore->init());

        $this->assertTrue($this->sqsQueueStore->enqueue($mailJob));

        $this->assertTrue($this->sqsQueueStore->isEmpty() === false);

        $mailJob = $this->sqsQueueStore->dequeue();
        $this->assertNull($this->sqsQueueStore->dequeue());

        $this->assertTrue($this->sqsQueueStore->isEmpty() === true);

        $this->assertTrue(!empty($mailJob->getMessage()));

        $dequeuedMailMessage = MailMessage::fromArray(json_decode($mailJob->getMessage(), true));

        $this->assertEquals(FixtureHelper::getMailMessage(), $dequeuedMailMessage);

        $mailJob->setDeleted(true);
        $this->sqsQueueStore->ack($mailJob);

        $this->assertTrue($this->sqsQueueStore->dequeue() === null);
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testBadMethodCallExceptionOnAck()
    {
        $mailJob = FixtureHelper::getPdoMailJob();
        $this->sqsQueueStore->ack($mailJob);
    }
}
