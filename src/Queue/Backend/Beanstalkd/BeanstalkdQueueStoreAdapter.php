<?php
namespace Da\Mailer\Queue\Backend\Beanstalk;

use Da\Mailer\Exception\InvalidCallException;
use Da\Mailer\Queue\Backend\MailJobInterface;
use Da\Mailer\Queue\Backend\QueueStoreAdapterInterface;
use Pheanstalk\Job as PheanstalkJob;
use Pheanstalk\Pheanstalk;
use phpseclib\Crypt\Random;

class BeanstalkdQueueStoreAdapter implements QueueStoreAdapterInterface
{
    /**
     * @var string the queue name
     */
    private $queueName;
    /**
     * @var int the time to run. Defaults to Pheanstalkd::DEFAULT_TTR.
     */
    private $timeToRun;
    /**
     * @var BeanstalkdQueueStoreConnection
     */
    protected $connection;

    /**
     * BeanstalkdQueueStoreAdapter constructor.
     *
     * @param BeanstalkdQueueStoreConnection $connection
     * @param string $queueName
     * @param int $timeToRun
     */
    public function __construct(
        BeanstalkdQueueStoreConnection $connection,
        $queueName = 'mail_queue',
        $timeToRun = Pheanstalk::DEFAULT_TTR
    ) {
        $this->connection = $connection;
        $this->queueName = $queueName;
        $this->timeToRun = $timeToRun;
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
        if ($payload === false) {
            var_dump(json_last_error_msg());
            ob_flush();
        }
        $delay = (int)max(Pheanstalk::DEFAULT_DELAY, $timestamp - time());

        return $this->getConnection()
            ->getInstance()
            ->useTube($this->queueName)
            ->put($payload, Pheanstalk::DEFAULT_PRIORITY, $delay, $this->timeToRun);
    }

    public function dequeue()
    {
        $job = $this->getConnection()->getInstance()->watchOnly($this->queueName)->reserve(0);
        if ($job instanceof PheanstalkJob) {
            $data = json_decode($job->getData(), true);

            return new BeanstalkdMailJob(
                [
                    'id' => $data['id'],
                    'attempt' => $data['attempt'],
                    'message' => $data['message'],
                    'pheanstalkJob' => $job
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
        return (int)$stats->current_jobs_delayed === 0
        && (int)$stats->current_jobs_urgent === 0
        && (int)$stats->current_jobs_ready === 0;

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
