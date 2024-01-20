<?php
namespace Da\Mailer\Queue\Backend\Redis;

use Da\Mailer\Exception\InvalidCallException;
use Da\Mailer\Queue\Backend\MailJobInterface;
use Da\Mailer\Queue\Backend\QueueStoreAdapterInterface;
use phpseclib3\Crypt\Random;

class RedisQueueStoreAdapter implements QueueStoreAdapterInterface
{
    /**
     * @var int
     */
    private $expireTime;
    /**
     * @var string
     */
    private $queueName;
    /**
     * @var RedisQueueStoreConnection
     */
    protected $connection;

    /**
     * RedisQueueStoreAdapter constructor.
     *
     * @param RedisQueueStoreConnection $connection
     * @param string $queueName
     * @param int $expireTime
     */
    public function __construct(RedisQueueStoreConnection $connection, $queueName = 'mail_queue', $expireTime = 60)
    {
        $this->expireTime = $expireTime;
        $this->connection = $connection;
        $this->queueName = $queueName;
        $this->init();
    }

    /**
     * @return RedisQueueStoreAdapter
     */
    public function init()
    {
        $this->getConnection()
            ->connect();

        return $this;
    }

    /**
     * @return RedisQueueStoreConnection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param RedisMailJob|MailJobInterface $mailJob
     *
     * @return int
     */
    public function enqueue(MailJobInterface $mailJob)
    {
        $timestamp = $mailJob->getTimeToSend();
        $payload = $this->createPayload($mailJob);

        return $timestamp !== null && $timestamp > time()
            ? $this->getConnection()->getInstance()->zadd($this->queueName . ':delayed', $timestamp, $payload)
            : $this->getConnection()->getInstance()->rpush($this->queueName, $payload);
    }

    /**
     * @return RedisMailJob|null
     */
    public function dequeue()
    {
        $this->migrateExpiredJobs();

        $job = $this->getConnection()->getInstance()->lpop($this->queueName);

        if ($job !== null) {
            $this->getConnection()
                ->getInstance()
                ->zadd($this->queueName . ':reserved', time() + $this->expireTime, $job);

            $data = json_decode($job, true);

            return new RedisMailJob(
                [
                    'id' => $data['id'],
                    'attempt' => $data['attempt'],
                    'message' => $data['message'],
                ]
            );
        }

        return null;
    }

    /**
     * @param RedisMailJob|MailJobInterface $mailJob
     */
    public function ack(MailJobInterface $mailJob)
    {
        if ($mailJob->isNewRecord()) {
            throw new InvalidCallException('RedisMailJob cannot be a new object to be acknowledged');
        }

        $this->removeReserved($mailJob);

        if (!$mailJob->isCompleted()) {
            if ($mailJob->getTimeToSend() === null || $mailJob->getTimeToSend() < time()) {
                $mailJob->setTimeToSend(time() + $this->expireTime);
            }
            $this->enqueue($mailJob);
        }
    }

    /**
     * @param MailJobInterface $mailJob
     */
    public function removeReserved(MailJobInterface $mailJob)
    {
        $payload = $this->createPayload($mailJob);
        $this->getConnection()->getInstance()->zrem($this->queueName . ':reserved', $payload);
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        return $this->getConnection()->getInstance()->llen($this->queueName) === 0;
    }

    /**
     * @param RedisMailJob|MailJobInterface $mailJob
     *
     * @return string
     */
    protected function createPayload(MailJobInterface $mailJob)
    {
        return json_encode(
            [
                'id' => $mailJob->isNewRecord() ? sha1(Random::string(32)) : $mailJob->getId(),
                'attempt' => $mailJob->getAttempt(),
                'message' => $mailJob->getMessage(),
            ]
        );
    }

    /**
     * Migrates all expired jobs from delayed and reserved queues to the main queue to be processed.
     */
    protected function migrateExpiredJobs()
    {
        $this->migrateJobs($this->queueName . ':delayed', $this->queueName);
        $this->migrateJobs($this->queueName . ':reserved', $this->queueName);
    }

    /**
     * Migrates expired jobs from one queue to another.
     *
     * @param string $from the name of the source queue
     * @param string $to the name of the target queue
     */
    protected function migrateJobs($from, $to)
    {
        $options = ['cas' => true, 'watch' => $from, 'retry' => 10];

        $this->getConnection()->getInstance()->transaction(
            $options,
            function ($transaction) use ($from, $to) {
                $time = time();
                // First we need to get all of jobs that have expired based on the current time
                // so that we can push them onto the main queue. After we get them we simply
                // remove them from this "delay" queues. All of this within a transaction.
                $jobs = $this->getExpiredJobs($transaction, $from, $time);
                // If we actually found any jobs, we will remove them from the old queue and we
                // will insert them onto the new (ready) "queue". This means they will stand
                // ready to be processed by the queue worker whenever their turn comes up.
                if (count($jobs) > 0) {
                    $this->removeExpiredJobs($transaction, $from, $time);
                    $this->pushExpiredJobsOntoNewQueue($transaction, $to, $jobs);
                }
            }
        );
    }

    /**
     * Get the expired jobs from a given queue.
     *
     * @param  \Predis\Transaction\MultiExec $transaction
     * @param  string $from
     * @param  int $time
     *
     * @return array
     */
    protected function getExpiredJobs($transaction, $from, $time)
    {
        return $transaction->zrangebyscore($from, '-inf', $time);
    }

    /**
     * Remove the expired jobs from a given queue.
     *
     * @param  \Predis\Transaction\MultiExec $transaction
     * @param  string $from
     * @param  int $time
     *
     */
    protected function removeExpiredJobs($transaction, $from, $time)
    {
        $transaction->multi();
        $transaction->zremrangebyscore($from, '-inf', $time);
    }

    /**
     * Push all of the given jobs onto another queue.
     *
     * @param  \Predis\Transaction\MultiExec $transaction
     * @param  string $to
     * @param  array $jobs
     *
     */
    protected function pushExpiredJobsOntoNewQueue($transaction, $to, $jobs)
    {
        call_user_func_array([$transaction, 'rpush'], array_merge([$to], $jobs));
    }
}
