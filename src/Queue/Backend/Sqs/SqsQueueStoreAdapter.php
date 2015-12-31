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
     * @return bool whether it has been successfully queued or not
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

    /**
     * Returns a MailJob fetched from Amazon SQS.
     *
     * @return MailJobInterface|SqsMailJob
     */
    public function dequeue()
    {
        $result = $this->getConnection()->getInstance()->receiveMessage([
            'QueueUrl' => $this->queueUrl,
        ]);
        $result = $result->getPath('Messages/*');

        return new SqsMailJob([
            'id' => $result['MessageId'],
            'receiptHandle' => $result['ReceiptHandle'],
            'message' => $result['Body'],
        ]);
    }

    /**
     * @param MailJobInterface|SqsMailJob $mailJob
     */
    public function ack(MailJobInterface $mailJob)
    {
        if ($mailJob->getDeleted()) {
            $this->getConnection()->getInstance()->deleteMessage([
                'QueueUrl' => $this->queueUrl,
                'ReceiptHandle' => $mailJob->getReceiptHandle(),
            ]);
        } elseif ($mailJob->getVisibilityTimeout() !== null) {
            $this->getConnection()->getInstance()->deleteMessage([
                'QueueUrl' => $this->queueUrl,
                'ReceiptHandle' => $mailJob->getReceiptHandle(),
                'VisibilityTimeout' => $mailJob->getVisibilityTimeout(),
            ]);
        }
    }

    public function isEmpty()
    {
    }
}
