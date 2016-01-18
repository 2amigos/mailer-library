<?php
namespace Da\Mailer\Test\Queue\Backend\Redis;

use Da\Mailer\Model\MailMessage;
use Da\Mailer\Queue\Backend\Redis\RedisQueueStoreAdapter;
use Da\Mailer\Queue\Backend\Redis\RedisQueueStoreConnection;
use Da\Mailer\Test\Fixture\FixtureHelper;
use PHPUnit_Framework_TestCase;
use Mockery;

class RedisQueueStoreAdapterTest extends PHPUnit_Framework_TestCase
{
    private $mailJob;
    private $payload;

    protected function setUp()
    {
        parent::setUp();
        $this->mailJob = FixtureHelper::getRedisMailJob();
        $this->payload = json_encode([
            'id' => '123456789',
            'attempt' => $this->mailJob->getAttempt(),
            'message' => $this->mailJob->getMessage()
        ]);
    }

    public function testEnqueueDequeueAndAcknowledge()
    {
        $payload = $this->payload;
        $redisClient = Mockery::mock('\Predis\Client')
            ->shouldReceive('zadd')
                ->andReturn(1)
            ->shouldReceive('rpush')
                ->andReturn(1)
            ->shouldReceive('llen')
                ->twice()
                ->andReturnUsing(
                    function (){
                        static $f = false;
                        return $f = !$f ? 1 : 0;
                    }
                )
            ->shouldReceive('zrem')
            ->andReturn(1)
            ->shouldReceive('lpop')
            ->with(Mockery::mustBe('mail_queue'))
            ->andReturnUsing(function() use ($payload) {
                static $f = false;
                return $f = !$f ? $payload : null;
            })
            ->shouldReceive('transaction')
            ->andReturn(1)
            ->getMock();

        $redisStoreConnection = Mockery::mock('\Da\Mailer\Queue\Backend\Redis\RedisQueueStoreConnection')
            ->shouldReceive('connect')
            ->andReturnSelf()
            ->shouldReceive('getInstance')
            ->andReturn($redisClient)
            ->getMock();

        $redisQueueStore = new RedisQueueStoreAdapter($redisStoreConnection);

        $this->assertSame($redisQueueStore, $redisQueueStore->init());
        $this->assertTrue($redisQueueStore->enqueue($this->mailJob) === 1);

        $this->assertTrue($redisQueueStore->isEmpty() === false);

        $mailJob = $redisQueueStore->dequeue();

        $this->assertTrue($redisQueueStore->isEmpty() === true);

        $this->assertTrue(!empty($mailJob->getMessage()));

        $dequeuedMailMessage = MailMessage::fromArray(json_decode($mailJob->getMessage(), true));

        $this->assertEquals(FixtureHelper::getMailMessage(), $dequeuedMailMessage);

        $mailJob->markAsCompleted();
        $redisQueueStore->ack($mailJob);

        $this->assertTrue($redisQueueStore->dequeue() === null);
    }

    public function testEnqueDequeueWithDelay()
    {
        $time = time() + 2;

        $redisClient = Mockery::mock('\Predis\Client')
            ->shouldReceive('zadd')
            ->with('mail_queue:delayed', $time)
            ->twice()
            ->withAnyArgs()
            ->andReturn(1)
            ->shouldReceive('rpush')
            ->andReturn(1)
            ->shouldReceive('llen')
            ->once()
            ->andReturn(0)
            ->shouldReceive('lpop')
            ->with(Mockery::mustBe('mail_queue'))
            ->andReturn($this->payload)
            ->shouldReceive('zrem')
            ->with('mail_queue:reserved', $this->payload)
            ->shouldReceive('transaction')
            ->andReturn(1)
            ->getMock();

        $redisStoreConnection = Mockery::mock('\Da\Mailer\Queue\Backend\Redis\RedisQueueStoreConnection')
            ->shouldReceive('connect')
            ->andReturnSelf()
            ->shouldReceive('getInstance')
            ->andReturn($redisClient)
            ->getMock();

        $redisQueueStore = new RedisQueueStoreAdapter($redisStoreConnection);

        $mailJob = $this->mailJob;
        $mailJob->setTimeToSend($time);
        $this->assertTrue($redisQueueStore->enqueue($mailJob) === 1);
        $this->assertTrue($redisQueueStore->isEmpty() === true);
        sleep(3); // sleep three seconds to expire in delayed
        $mailJob = $redisQueueStore->dequeue(); // now it should have migrated

        $this->assertTrue(!empty($mailJob->getMessage()));

        $mailJob->markAsCompleted();
        $redisQueueStore->ack($mailJob);
    }

    public function testEnqueDequeueWithPossibleFailure()
    {
        $time = time() + 2;
        $redisClient = Mockery::mock('\Predis\Client')
            ->shouldReceive('rpush')
            ->once()
            ->andReturn(1)
            ->shouldReceive('zadd')
            ->twice()
            ->withAnyArgs()
            ->andReturn(1)
            ->shouldReceive('llen')
            ->twice()
            ->andReturnUsing(
                function (){
                    static $f = false;
                    return $f = !$f ? 1 : 0;
                }
            )
            ->shouldReceive('lpop')
            ->with('queue')
            ->andReturn($this->payload)
            ->shouldReceive('zrem')
            ->with('queue:reserved', $this->payload)
            ->andReturn(1)
            ->shouldReceive('transaction')
            ->andReturn(1)
            ->getMock();

        $redisStoreConnection = Mockery::mock('\Da\Mailer\Queue\Backend\Redis\RedisQueueStoreConnection')
            ->shouldReceive('connect')
            ->andReturnSelf()
            ->shouldReceive('getInstance')
            ->andReturn($redisClient)
            ->getMock();

        $redisQueueStore = new RedisQueueStoreAdapter($redisStoreConnection, 'queue', 2);

        $mailJob = FixtureHelper::getRedisMailJob();
        $this->assertSame($redisQueueStore, $redisQueueStore->init());
        $this->assertTrue($redisQueueStore->enqueue($mailJob) === 1);

        $this->assertTrue($redisQueueStore->isEmpty() === false);

        $mailJob = $redisQueueStore->dequeue(); // it should be in reserved

        sleep(3); // lets imagine we have a failure, wait 3 seconds

        $againMailJob = $redisQueueStore->dequeue(); // it should has come back and placed again in reserved
        $this->assertEquals($mailJob, $againMailJob);

        $mailJob->markAsCompleted();
        $redisQueueStore->ack($mailJob); // finish everything

        $this->assertTrue($redisQueueStore->isEmpty());
    }

    /**
     * @expectedException \Da\Mailer\Exception\InvalidCallException
     */
    public function testBadMethodCallExceptionOnAck()
    {
        $mailJob = FixtureHelper::getRedisMailJob();
        $connection = new RedisQueueStoreConnection([]);
        $redisQueueStore = new RedisQueueStoreAdapter($connection);
        $redisQueueStore->ack($mailJob);
    }

    public function testNonCompletedAck()
    {
        $payload = $this->payload;
        $redisClient = Mockery::mock('\Predis\Client')
            ->shouldReceive('zadd')
            ->times(2)
            ->andReturn(1)
            ->shouldReceive('rpush')
            ->andReturn(1)
            ->shouldReceive('llen')
            ->twice()
            ->andReturnUsing(
                function (){
                    static $f = false;
                    return $f = !$f ? 1 : 0;
                }
            )
            ->shouldReceive('zrem')
            ->andReturn(1)
            ->shouldReceive('lpop')
            ->with(Mockery::mustBe('mail_queue'))
            ->andReturnUsing(function() use ($payload) {
                static $f = false;
                return $f = !$f ? $payload : null;
            })
            ->shouldReceive('transaction')
            ->andReturn(1)
            ->getMock();

        $redisStoreConnection = Mockery::mock('\Da\Mailer\Queue\Backend\Redis\RedisQueueStoreConnection')
            ->shouldReceive('connect')
            ->andReturnSelf()
            ->shouldReceive('getInstance')
            ->andReturn($redisClient)
            ->getMock();

        $redisQueueStore = new RedisQueueStoreAdapter($redisStoreConnection);

        $this->assertSame($redisQueueStore, $redisQueueStore->init());
        $this->assertTrue($redisQueueStore->enqueue($this->mailJob) === 1);

        $this->assertTrue($redisQueueStore->isEmpty() === false);

        $mailJob = $redisQueueStore->dequeue();

        $this->assertTrue($redisQueueStore->isEmpty() === true);

        $this->assertTrue(!empty($mailJob->getMessage()));

        $dequeuedMailMessage = MailMessage::fromArray(json_decode($mailJob->getMessage(), true));

        $this->assertEquals(FixtureHelper::getMailMessage(), $dequeuedMailMessage);
        $redisQueueStore->ack($mailJob);
    }
}
