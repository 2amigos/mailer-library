<?php
namespace Da\Mailer\Queue\Backend\Sqs;

use Da\Mailer\Queue\Backend\MailJobInterface;
use Da\Mailer\Queue\Backend\QueueStoreAdapterInterface;

class SqsQueueStoreAdapter implements QueueStoreAdapterInterface
{
    /**
     * @var string the name of the queue to store the messages.
     */
    private $queueName;
    /**
     * @var string
     */
    private $queueUrl;
    /**
     * @var SqsQueueStoreAdapter
     */
    protected $connection;

    /**
     * PdoQueueStoreAdapter constructor.
     *
     * @param SqsQueueStoreConnection $connection
     * @param string $queueName the name of the queue in the SQS where the mail jobs are stored
     */
    public function __construct(SqsQueueStoreConnection $connection, $queueName = 'mail_queue')
    {
        $this->connection = $connection;
        $this->queueName = $queueName;
        $this->init();
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->getConnection()->connect();

        $queue = $this->getConnection()->getInstance()->createQueue(['QueueName' => $this->queueName]);
        $this->queueUrl = $queue->get('QueueUrl');

        return $this;
    }

    /**
     * @return SqsQueueStoreConnection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param MailJobInterface|SqsMailJob $mailJob
     */
    public function enqueue(MailJobInterface $mailJob)
    {
        $result = $this->getConnection()->getInstance()->sendMessage([
            'QueueUrl' => $this->queueUrl,
            'MessageBody' => $mailJob->getMessage(),
            'DelaySeconds' => $mailJob->getDelaySeconds(),
        ]);
        $messageId = $result->get('MessageId');
        return $messageId !== null && is_string($messageId);
    }

    public function dequeue()
    {
        $result = $this->getConnection()->getInstance()->receiveMessage(array(
            'QueueUrl' => $this->queueUrl,
        ));

        var_dump($result->get('Messages'));die;

//        while (($message = $result->getPath('Messages/*')) !== null) {
//            var_dump($message);
//        }
    }

    public function ack(MailJobInterface $mailJob)
    {

    }

    public function isEmpty()
    {

    }
}
