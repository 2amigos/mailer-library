<?php
namespace Da\Mailer\Queue\Backend\Beanstalkd;

use Da\Mailer\Model\MailJob;
use Pheanstalk\Job as PheanstalkJob;

class BeanstalkdMailJob extends MailJob
{
    /**
     * @var int the unix timestamp the job should be processed
     */
    private $timeToSend;
    /**
     * @var PheanstalkJob
     */
    private $pheanstalkJob;

    /**
     * @return int the timestamp
     */
    public function getTimeToSend()
    {
        return $this->timeToSend;
    }

    /**
     * @param int $timestamp
     */
    public function setTimeToSend($timestamp)
    {
        $this->timeToSend = $timestamp;
    }

    /**
     * @param PheanstalkJob $job
     */
    public function setPheanstalkJob(PheanstalkJob $job)
    {
        $this->pheanstalkJob = $job;
    }

    /**
     * @return PheanstalkJob
     */
    public function getPheanstalkJob()
    {
        return $this->pheanstalkJob;
    }
}
