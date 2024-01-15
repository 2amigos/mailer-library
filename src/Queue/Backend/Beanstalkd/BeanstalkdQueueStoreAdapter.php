<?php
namespace Da\Mailer\Queue\Backend\Beanstalkd;

use Da\Mailer\Exception\InvalidCallException;
use Da\Mailer\Queue\Backend\MailJobInterface;
use Da\Mailer\Queue\Backend\QueueStoreAdapterInterface;
use Pheanstalk\Job as PheanstalkJob;
use Pheanstalk\Pheanstalk;
use phpseclib3\Crypt\Random;

class BeanstalkdQueueStoreAdapter implements QueueStoreAdapterInterface
{
    /**
     * @var string the queue name
     */
    protected $queueName;
    /**
     * @var int the time to run. Defaults to Pheanstalkd::DEFAULT_TTR.
     */
    protected $timeToRun;
    /**
     * @var BeanstalkdQueueStoreConnection
     */
    protected $connection;
    /**
     * @var int Reserves/locks a ready job in a watched tube. A timeout value of 0 will cause the server to immediately
     * return either a response or TIMED_OUT.  A positive value of timeout will limit the amount of time the client will
     * block on the reserve request until a job becomes available.
     *
     * We highly recommend a non-zero value. Defaults to 5.
     */
    protected $reserveTimeout;

    /**
     * BeanstalkdQueueStoreAdapter constructor.
     *
     * @param BeanstalkdQueueStoreConnection $connection
     * @param string $queueName
     * @param int $timeToRun
     * @param int $reserveTimeOut
     */
    public function __construct(
        BeanstalkdQueueStoreConnection $connection,
        $queueName = 'mail_queue',
        $timeToRun = Pheanstalk::DEFAULT_TTR,
        $reserveTimeOut = 5
    ) {
        $this->connection = $connection;
        $this->queueName = $queueName;
        $this->timeToRun = $timeToRun;
        $this->reserveTimeout = $reserveTimeOut;
        $this->init();
    }

    /**
     * @return BeanstalkdQueueStoreAdapter
     */
    public function init()
    {
        $this->getConnection()->connect();

        return $this;
    }

    /**
     * @return BeanstalkdQueueStoreConnection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param BeanstalkdMailJob|MailJobInterface $mailJob
     *
     * @return int
     */
    public function enqueue(MailJobInterface $mailJob)
    {
        $timestamp = $mailJob->getTimeToSend();
        $payload = $this->createPayload($mailJob);
        $delay = (int) max(Pheanstalk::DEFAULT_DELAY, $timestamp - time());

        return $this->getConnection()
            ->getInstance()
            ->useTube($this->queueName)
            ->put($payload, Pheanstalk::DEFAULT_PRIORITY, $delay, $this->timeToRun);
    }

    /**
     * @return BeanstalkdMailJob|null
     */
    public function dequeue()
    {
        $job = $this->getConnection()->getInstance()->watch($this->queueName)->reserve($this->reserveTimeout);
        if ($job instanceof PheanstalkJob) {
            $data = json_decode($job->getData(), true);

            return new BeanstalkdMailJob(
                [
                    'id' => $data['id'],
                    'attempt' => $data['attempt'],
                    'message' => $data['message'],
                    'pheanstalkJob' => $job,
                ]
            );
        }

        return null;
    }

    /**
     * @param BeanstalkdMailJob|MailJobInterface $mailJob
     */
    public function ack(MailJobInterface $mailJob)
    {
        if ($mailJob->isNewRecord()) {
            throw new InvalidCallException('BeanstalkdMailJob cannot be a new object to be acknowledged');
        }

        $pheanstalk = $this->getConnection()->getInstance()->useTube($this->queueName);
        if ($mailJob->isCompleted()) {
            $pheanstalk->delete($mailJob->getPheanstalkJob());
        } else {
            $timestamp = $mailJob->getTimeToSend();
            $delay = max(0, $timestamp - time());

            // add back to the queue as it wasn't completed maybe due to some transitory error
            // could also be failed.
            $pheanstalk->release($mailJob->getPheanstalkJob(), Pheanstalk::DEFAULT_PRIORITY, $delay);
        }
    }

    /**
     *
     * @return bool
     */
    public function isEmpty()
    {
        $stats = $this->getConnection()->getInstance()->statsTube($this->queueName);

        return (int) $stats->current_jobs_delayed === 0
        && (int) $stats->current_jobs_urgent === 0
        && (int) $stats->current_jobs_ready === 0;
    }

    /**
     * @param BeanstalkdMailJob|MailJobInterface $mailJob
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
}
