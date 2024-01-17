<?php
namespace Da\Mailer\Test\Queue\Backend\Beanstalkd;

use Da\Mailer\Builder\QueueBuilder;
use Da\Mailer\Enum\MessageBrokerEnum;
use Da\Mailer\Model\MailMessage;
use Da\Mailer\Queue\Backend\Beanstalkd\BeanstalkdQueueStoreAdapter;
use Da\Mailer\Queue\Backend\Beanstalkd\BeanstalkdQueueStoreConnection;
use Da\Mailer\Test\Fixture\FixtureHelper;
use Mockery;
use Pheanstalk\Job;
use Pheanstalk\Pheanstalk;
use Pheanstalk\Response\ArrayResponse;
use PHPUnit\Framework\TestCase;

class BeanstalkdQueueStoreAdapterTest extends TestCase
{
    private $mailJob;
    private $payload;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mailJob = FixtureHelper::getBeanstalkdMailJob();
        $this->payload = json_encode(
            [
                'id' => '123456789',
                'attempt' => $this->mailJob->getAttempt(),
                'message' => $this->mailJob->getMessage(),
            ]
        );
    }

    public function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
    }

    public function testEnqueueDequeueAndAcknowledge()
    {
        $statsTubeResponse2 = new ArrayResponse(
            'test', [
                'current-jobs-delayed' => 0,
                'current-jobs-urgent' => 0,
                'current-jobs-ready' => 0,
            ]
        );
        $statsTubeResponse1 = new ArrayResponse('test', ['current-jobs-delayed' => 1]);
        $payload = json_decode($this->payload, true);
        $payload['job'] = new Job(1, 'demo');
        $btJob2 = Mockery::mock('\Pheanstalk\Job')
            ->shouldReceive('getData')
            ->andReturn(json_encode($payload))
            ->getMock();
        $btClient = Mockery::mock('\Pheanstalk\Pheanstalk')
            ->shouldReceive('useTube')
            ->with('mail_queue')
            ->andReturnSelf()
            ->shouldReceive('put')
            ->andReturn($payload['job'])
            ->shouldReceive('watch')
            ->andReturnSelf()
            ->shouldReceive('statsTube')
            ->twice()
            ->andReturn($statsTubeResponse1, $statsTubeResponse2)
            ->shouldReceive('reserveWithTimeout')
            ->with(5)
            ->andReturn($btJob2)
            ->shouldReceive('delete')
            ->andReturn(1)
            ->getMock();

        $btStoreConnection = Mockery::mock('\Da\Mailer\Queue\Backend\Beanstalkd\BeanstalkdQueueStoreConnection')
            ->shouldReceive('connect')
            ->andReturnSelf()
            ->shouldReceive('getInstance')
            ->andReturn($btClient)
            ->getMock();

        $btQueueStore = new BeanstalkdQueueStoreAdapter($btStoreConnection);

        $this->assertSame($btQueueStore, $btQueueStore->init());
        $this->assertTrue($btQueueStore->enqueue($this->mailJob) instanceof \Pheanstalk\Job);

        $this->assertTrue($btQueueStore->isEmpty() === false);

        $mailJob = $btQueueStore->dequeue();

        $this->assertTrue($btQueueStore->isEmpty() === true);

        $this->assertTrue(!empty($mailJob->getMessage()));

        $dequeuedMailMessage = MailMessage::fromArray(json_decode($mailJob->getMessage(), true));

        $this->assertEquals(FixtureHelper::getMailMessage(), $dequeuedMailMessage);
        $this->assertTrue($mailJob->getPheanstalkJob() instanceof Job);

        $mailJob->markAsCompleted();
        $btQueueStore->ack($mailJob);

        // TODO fix dequeue assertion
        #$this->assertNull($btQueueStore->dequeue());
    }

    public function testEnqueDequeueWithDelay()
    {
        $time = time() + 2;
        $payload = json_decode($this->payload, true);
        $payload['job'] = new Job(1, 'demo');
        $btJob2 = Mockery::mock('\Pheanstalk\Job')
            ->shouldReceive('getData')
            ->andReturn(json_encode($payload))
            ->getMock();
        $btClient = Mockery::mock('\Pheanstalk\Pheanstalk')
            ->shouldReceive('useTube')
            ->with(Mockery::mustBe('mail_queue'))
            ->andReturnSelf()
            ->shouldReceive('put')
            ->withAnyArgs()
            ->andReturn($payload['job'])
            ->shouldReceive('watch')
            ->with(Mockery::mustBe('mail_queue'))
            ->andReturnSelf()
            ->shouldReceive('reserveWithTimeout')
            ->with(5)
            ->andReturn(null, $btJob2)
            ->shouldReceive('delete')
            ->andReturn(1)
            ->getMock();

        $btConnection = Mockery::mock('\Da\Mailer\Queue\Backend\Beanstalkd\BeanstalkdQueueStoreConnection')
            ->shouldReceive('connect')
            ->andReturnSelf()
            ->shouldReceive('getInstance')
            ->andReturn($btClient)
            ->getMock();

        $btQueueStore = new BeanstalkdQueueStoreAdapter($btConnection);

        $mailJob = $this->mailJob;
        $mailJob->setTimeToSend($time);
        $this->assertTrue($btQueueStore->enqueue($mailJob) instanceof Job);
        $this->assertTrue($btQueueStore->dequeue() === null);
        sleep(3); // sleep three seconds to expire in delayed
        $mailJob = $btQueueStore->dequeue(); // now it should have migrated

        $this->assertTrue(!empty($mailJob->getMessage()));

        $mailJob->markAsCompleted();
        $btQueueStore->ack($mailJob);
    }

    public function testBadMethodCallExceptionOnAck()
    {
        $this->expectException(\Da\Mailer\Exception\InvalidCallException::class);

        $mailJob = FixtureHelper::getBeanstalkdMailJob();
        $btConnection = Mockery::mock('\Da\Mailer\Queue\Backend\Beanstalkd\BeanstalkdQueueStoreConnection')
            ->shouldReceive('connect')
            ->andReturnSelf()
            ->shouldReceive('getInstance')
            ->getMock();

        $btQueueStore = new BeanstalkdQueueStoreAdapter($btConnection);
        $btQueueStore->ack($mailJob);
    }

    public function testNonCompletedAck()
    {
        $statsTubeResponse1 = new ArrayResponse('test', ['current-jobs-delayed' => 1]);
        $statsTubeResponse2 = new ArrayResponse(
            'test', [
                'current-jobs-delayed' => 0,
                'current-jobs-urgent' => 0,
                'current-jobs-ready' => 0,
            ]
        );
        $payload = json_decode($this->payload, true);
        $payload['job'] = new Job(1, 'demo');
        $btJob2 = Mockery::mock('\Pheanstalk\Job')
            ->shouldReceive('getData')
            ->andReturn(json_encode($payload))
            ->getMock();
        $btClient = Mockery::mock('\Pheanstalk\Pheanstalk')
            ->shouldReceive('useTube')
            ->with(Mockery::mustBe('mail_queue'))
            ->andReturnSelf()
            ->shouldReceive('put')
            ->withAnyArgs()
            ->andReturn($payload['job'])
            ->shouldReceive('statsTube')
            ->twice()
            ->andReturn($statsTubeResponse1, $statsTubeResponse2)
            ->shouldReceive('watch')
            ->with(Mockery::mustBe('mail_queue'))
            ->andReturnSelf()
            ->shouldReceive('reserveWithTimeout')
            ->with(5)
            ->andReturn($btJob2)
            ->shouldReceive('release')
            ->andReturn(1)
            ->shouldReceive('delete')
            ->andReturn(1)
            ->getMock();

        $btConnection = Mockery::mock('\Da\Mailer\Queue\Backend\Beanstalkd\BeanstalkdQueueStoreConnection')
            ->shouldReceive('connect')
            ->andReturnSelf()
            ->shouldReceive('getInstance')
            ->andReturn($btClient)
            ->getMock();

        $btQueueStore = new BeanstalkdQueueStoreAdapter($btConnection);

        $this->assertSame($btQueueStore, $btQueueStore->init());
        $this->assertTrue($btQueueStore->enqueue($this->mailJob) instanceof Job);

        $this->assertTrue($btQueueStore->isEmpty() === false);

        $mailJob = $btQueueStore->dequeue();

        $this->assertTrue($btQueueStore->isEmpty() === true);

        $this->assertTrue(!empty($mailJob->getMessage()));

        $dequeuedMailMessage = MailMessage::fromArray(json_decode($mailJob->getMessage(), true));

        $this->assertEquals(FixtureHelper::getMailMessage(), $dequeuedMailMessage);
        $btQueueStore->ack($mailJob);
    }
}
