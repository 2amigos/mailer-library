# SQS Backend Usage

## Add email job to queue

```php
use Da\Mailer\Model\MailMessage;
use Da\Mailer\Queue\MailQueue;
use Da\Mailer\Queue\Backend\Sqs\SqsMailJob;
use Da\Mailer\Queue\Backend\Sqs\SqsQueueStoreAdapter;
use Da\Mailer\Queue\Backend\Sqs\SqsQueueStoreConnection;

$message = new MailMessage([
    'from' => 'sarah.connor@gmail.com',
    'to' => 'john.connor@gmail.com',
    'subject' => 'What is up?',
    'bodyText' => 'New mailing'
]);

$conn = new SqsQueueStoreConnection([
    'key' => 'AKIA...',
    'secret' => '...',
    'region' => 'eu-west-1', // https://docs.aws.amazon.com/general/latest/gr/rande.html
]);

$adapter = new SqsQueueStoreAdapter($conn);

$queue = new MailQueue($adapter);

$job = new SqsMailJob([
    'message' => json_encode($message),
]);

if (!$queue->enqueue($job)) {
    // ... queue operation failed
}
```

## Fetch email job from queue

```php
use Da\Mailer\Queue\MailQueue;
use Da\Mailer\Queue\Backend\Sqs\SqsQueueStoreAdapter;
use Da\Mailer\Queue\Backend\Sqs\SqsQueueStoreConnection;

$conn = new SqsQueueStoreConnection([
    'key' => 'AKIA...',
    'secret' => '...',
    'region' => 'eu-west-1', // https://docs.aws.amazon.com/general/latest/gr/rande.html
]);

$adapter = new SqsQueueStoreAdapter($conn);

$queue = new MailQueue($adapter);

if (($job = $queue->dequeue()) !== null) {
    // ... do something with received job
    // ... send it using `mail()` function for example

    $job->setDeleted(true);
    $queue->ack($job);
}
```

## Send email job with `mail()` function

```php
use Da\Mailer\Mailer;
use Da\Mailer\Model\MailMessage;
use Da\Mailer\Transport\MailTransport;

$transport = new MailTransport();

$mailer = new Mailer($transport);

$mailJob = /* ... get mail job here ... */;

$status = null;

try {
    $result = $mailer->send(
        new MailMessage(json_decode($mailJob->getMessage(), true))
    );
} catch (Exception $e) {
    // log exception
}

if (is_null($status)) {
    // ... cannot send email
}
```
