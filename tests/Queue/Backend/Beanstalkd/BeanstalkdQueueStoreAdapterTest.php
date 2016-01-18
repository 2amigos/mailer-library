<?php
namespace Da\Mailer\Test\Queue\Backend\Redis;

use Da\Mailer\Model\MailMessage;
use Da\Mailer\Queue\Backend\Beanstalk\BeanstalkdQueueStoreAdapter;
use Da\Mailer\Queue\Backend\Beanstalk\BeanstalkdQueueStoreConnection;
use Da\Mailer\Test\Fixture\FixtureHelper;
use Mockery;
use Pheanstalk\Job;
use Pheanstalk\Response\ArrayResponse;
use PHPUnit_Framework_TestCase;

class BeanstalkdQueueStoreAdapterTest extends PHPUnit_Framework_TestCase
{
    private $mailJob;
    private $payload;

    protected function setUp()
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
            ->andReturn(1)
            ->shouldReceive('watchOnly')
            ->andReturnSelf()
            ->shouldReceive('statsTube')
            ->twice()
            ->andReturn($statsTubeResponse1, $statsTubeResponse2)
            ->shouldReceive('reserve')
            ->with(0)
            ->andReturn($btJob2, null)
            ->shouldReceive('delete')
            ->andReturn(1)
            ->getMock();

        $btStoreConnection = Mockery::mock('\Da\Mailer\Queue\Backend\Beanstalk\BeanstalkdQueueStoreConnection')
            ->shouldReceive('connect')
            ->andReturnSelf()
            ->shouldReceive('getInstance')
            ->andReturn($btClient)
            ->getMock();

        $btQueueStore = new BeanstalkdQueueStoreAdapter($btStoreConnection);

        $this->assertSame($btQueueStore, $btQueueStore->init());
        $this->assertTrue($btQueueStore->enqueue($this->mailJob) > 0);

        $this->assertTrue($btQueueStore->isEmpty() === false);

        $mailJob = $btQueueStore->dequeue();

        $this->assertTrue($btQueueStore->isEmpty() === true);

        $this->assertTrue(!empty($mailJob->getMessage()));

        $dequeuedMailMessage = MailMessage::fromArray(json_decode($mailJob->getMessage(), true));

        $this->assertEquals(FixtureHelper::getMailMessage(), $dequeuedMailMessage);

        $mailJob->markAsCompleted();
        $btQueueStore->ack($mailJob);

        $this->assertTrue($btQueueStore->dequeue() === null);
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
            ->andReturn(1)
            ->shouldReceive('watchOnly')
            ->with(Mockery::mustBe('mail_queue'))
            ->andReturnSelf()
            ->shouldReceive('reserve')
            ->with(0)
            ->andReturn(null, $btJob2)
            ->shouldReceive('delete')
            ->andReturn(1)
            ->getMock();

        $btConnection = Mockery::mock('\Da\Mailer\Queue\Backend\Beanstalk\BeanstalkdQueueStoreConnection')
            ->shouldReceive('connect')
            ->andReturnSelf()
            ->shouldReceive('getInstance')
            ->andReturn($btClient)
            ->getMock();

        $btQueueStore = new BeanstalkdQueueStoreAdapter($btConnection);

        $mailJob = $this->mailJob;
        $mailJob->setTimeToSend($time);
        $this->assertTrue($btQueueStore->enqueue($mailJob) > 0);
        $this->assertTrue($btQueueStore->dequeue() === null);
        sleep(3); // sleep three seconds to expire in delayed
        $mailJob = $btQueueStore->dequeue(); // now it should have migrated

        $this->assertTrue(!empty($mailJob->getMessage()));

        $mailJob->markAsCompleted();
        $btQueueStore->ack($mailJob);
    }

    /**
     * @expectedException \Da\Mailer\Exception\InvalidCallException
     */
    public function testBadMethodCallExceptionOnAck()
    {
        $mailJob = FixtureHelper::getBeanstalkdMailJob();
        $connection = new BeanstalkdQueueStoreConnection([]);
        $btQueueStore = new BeanstalkdQueueStoreAdapter($connection);
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
            ->andReturn(3)
            ->shouldReceive('statsTube')
            ->twice()
            ->andReturn($statsTubeResponse1, $statsTubeResponse2)
            ->shouldReceive('watchOnly')
            ->with(Mockery::mustBe('mail_queue'))
            ->andReturnSelf()
            ->shouldReceive('reserve')
            ->with(0)
            ->andReturn($btJob2)
            ->shouldReceive('release')
            ->andReturn(1)
            ->shouldReceive('delete')
            ->andReturn(1)
            ->getMock();

        $btConnection = Mockery::mock('\Da\Mailer\Queue\Backend\Beanstalk\BeanstalkdQueueStoreConnection')
            ->shouldReceive('connect')
            ->andReturnSelf()
            ->shouldReceive('getInstance')
            ->andReturn($btClient)
            ->getMock();

        $btQueueStore = new BeanstalkdQueueStoreAdapter($btConnection);

        $this->assertSame($btQueueStore, $btQueueStore->init());
        $this->assertTrue($btQueueStore->enqueue($this->mailJob) > 1);

        $this->assertTrue($btQueueStore->isEmpty() === false);

        $mailJob = $btQueueStore->dequeue();

        $this->assertTrue($btQueueStore->isEmpty() === true);

        $this->assertTrue(!empty($mailJob->getMessage()));

        $dequeuedMailMessage = MailMessage::fromArray(json_decode($mailJob->getMessage(), true));

        $this->assertEquals(FixtureHelper::getMailMessage(), $dequeuedMailMessage);
        $btQueueStore->ack($mailJob);
    }
}
