<?php
namespace Da\tests\Queue\Backend\Sqs;

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
    private $sqsQueueStore1, $sqsQueueStore2;

    protected function setUp()
    {
        // prepare sqs response collections - begin
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
                    'Attempt' => 1
                ],
            ],
        ]);
        $receiveMessageResult2 = new Collection([
            // no message(s) returned by Amazon SQS
        ]);
        // prepare sqs response collections - end

        // ------------------------------------------------------------

        // prepare queue store 1 - begin
        /** @var SqsClient $sqsClient1 */
        $sqsClient1 = Mockery::mock('\Aws\Sqs\SqsClient')
            ->shouldReceive('createQueue')
                ->with(Mockery::mustBe([
                    'QueueName' => 'testing_queue_1',
                ]))
                ->andReturn($createQueueResult)
            ->shouldReceive('sendMessage')
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

        /** @var SqsQueueStoreConnection $sqsQueueStoreConnection1 */
        $sqsQueueStoreConnection1 = Mockery::mock('\Da\Mailer\Queue\Backend\Sqs\SqsQueueStoreConnection')
            ->shouldReceive('connect')
                ->andReturnSelf()
            ->shouldReceive('getInstance')
                ->andReturn($sqsClient1)
            ->getMock();

        $this->sqsQueueStore1 = new SqsQueueStoreAdapter($sqsQueueStoreConnection1, 'testing_queue_1');
        // prepare queue store 1 - end

        // ------------------------------------------------------------

        // prepare queue store 2 - begin
        /** @var SqsClient $sqsClient1 */
        $sqsClient2 = Mockery::mock('\Aws\Sqs\SqsClient')
            ->shouldReceive('createQueue')
                ->with(Mockery::mustBe([
                    'QueueName' => 'testing_queue_2',
                ]))
                ->andReturn($createQueueResult)
            ->shouldReceive('sendMessage')
                ->andReturn($sendMessageResult)
            ->shouldReceive('getQueueAttributes')
                ->with(Mockery::mustBe([
                    'QueueUrl' => 'http://queue.url/path/',
                    'AttributeNames' => ['ApproximateNumberOfMessages'],
                ]))
                ->andReturn($getQueueAttributesResult1, $getQueueAttributesResult2, $getQueueAttributesResult1)
            ->shouldReceive('receiveMessage')
                ->with(Mockery::mustBe([
                    'QueueUrl' => 'http://queue.url/path/',
                ]))
                ->andReturn($receiveMessageResult1, $receiveMessageResult2)
            ->shouldReceive('changeMessageVisibility')
                ->with(Mockery::mustBe([
                    'QueueUrl' => 'http://queue.url/path/',
                    'ReceiptHandle' => 'receiveMessageResult1Handle',
                    'VisibilityTimeout' => 5,
                ]))
            ->getMock();

        /** @var SqsQueueStoreConnection $sqsQueueStoreConnection1 */
        $sqsQueueStoreConnection1 = Mockery::mock('\Da\Mailer\Queue\Backend\Sqs\SqsQueueStoreConnection')
            ->shouldReceive('connect')
                ->andReturnSelf()
            ->shouldReceive('getInstance')
                ->andReturn($sqsClient2)
            ->getMock();

        $this->sqsQueueStore2 = new SqsQueueStoreAdapter($sqsQueueStoreConnection1, 'testing_queue_2');
        // prepare queue store 2 - end
    }

    public function tearDown()
    {
        Mockery::close();

        parent::tearDown();
    }

    public function testEnqueueDequeueAndAcknowledge()
    {
        $mailJob = FixtureHelper::getSqsMailJob();

        $this->assertSame($this->sqsQueueStore1, $this->sqsQueueStore1->init());

        $this->assertTrue($this->sqsQueueStore1->enqueue($mailJob));

        $this->assertFalse($this->sqsQueueStore1->isEmpty());

        $mailJob = $this->sqsQueueStore1->dequeue();
        $this->assertNull($this->sqsQueueStore1->dequeue());
        $this->assertSame('receiveMessageResult1Id', $mailJob->getId());

        $this->assertTrue($this->sqsQueueStore1->isEmpty());

        $this->assertTrue(!empty($mailJob->getMessage()));

        $dequeuedMailMessage = MailMessage::fromArray(json_decode($mailJob->getMessage(), true));

        $this->assertEquals(FixtureHelper::getMailMessage(), $dequeuedMailMessage);

        $mailJob->setDeleted(true);
        $this->sqsQueueStore1->ack($mailJob);

        $this->assertNull($this->sqsQueueStore1->dequeue());
    }

    public function testAcknowledgementToUpdateMailJobs()
    {
        $mailJob = FixtureHelper::getSqsMailJob();

        $this->sqsQueueStore2->enqueue($mailJob);
        $this->assertFalse($this->sqsQueueStore2->isEmpty());
        $dequedMailJob = $this->sqsQueueStore2->dequeue();
        $this->assertNull($this->sqsQueueStore2->dequeue());
        $this->assertTrue($this->sqsQueueStore2->isEmpty());
        // set visibility timeout to five seconds
        $dequedMailJob->setVisibilityTimeout(5);
        $this->assertEquals(5, $dequedMailJob->getVisibilityTimeout());
        $this->sqsQueueStore2->ack($dequedMailJob);
        $this->assertFalse($this->sqsQueueStore2->isEmpty());
    }

    public function testDoNothingWithMailJob()
    {
        $mailJob = FixtureHelper::getSqsMailJob();

        $this->sqsQueueStore2->enqueue($mailJob);
        $this->assertFalse($this->sqsQueueStore2->isEmpty());
        $dequedMailJob = $this->sqsQueueStore2->dequeue();
        $this->assertNull($this->sqsQueueStore2->dequeue());
        $this->assertTrue($this->sqsQueueStore2->isEmpty());
        $this->assertFalse($this->sqsQueueStore2->ack($dequedMailJob));
        $this->assertFalse($this->sqsQueueStore2->isEmpty());
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testBadMethodCallExceptionOnAck()
    {
        $mailJob = FixtureHelper::getPdoMailJob();
        $this->sqsQueueStore1->ack($mailJob);
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testBadMethodCallExceptionOnSetDelaySeconds()
    {
        $mailJob = FixtureHelper::getSqsMailJob();
        $mailJob->setDelaySeconds(900);
        $this->assertEquals(900, $mailJob->getDelaySeconds());
        $mailJob->setDelaySeconds(901);
    }
}
