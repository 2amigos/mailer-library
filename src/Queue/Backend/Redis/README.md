# Redis Backend Usage

## Add email job to queue

```php
use Da\Mailer\Model\MailMessage;
use Da\Mailer\Queue\MailQueue;
use Da\Mailer\Queue\Backend\Redis\RedisMailJob;
use Da\Mailer\Queue\Backend\Redis\RedisQueueStoreAdapter;
use Da\Mailer\Queue\Backend\Redis\RedisQueueStoreConnection;

$message = new MailMessage([
    'from' => 'sarah.connor@gmail.com',
    'to' => 'john.connor@gmail.com',
    'subject' => 'What is up?',
]);

$conn = new RedisQueueStoreConnection([
    'host' => 'localhost',
    'port' => 9367,
]);

$adapter = new RedisQueueStoreAdapter($conn);

$queue = new MailQueue($adapter);

$job = new RedisMailJob([
    'message' => json_encode($message),
]);

if (!$queue->enqueue($job)) {
    // ... queue operation failed
}
```

## Fetch email job from queue

```php
use Da\Mailer\Queue\MailQueue;
use Da\Mailer\Queue\Backend\Redis\RedisQueueStoreAdapter;
use Da\Mailer\Queue\Backend\Redis\RedisQueueStoreConnection;

$conn = new RedisQueueStoreConnection([
    'host' => 'localhost',
    'port' => 9367,
]);

$adapter = new RedisQueueStoreAdapter($conn);

$queue = new MailQueue($adapter);

if (($job = $queue->dequeue()) !== null) {
    // ... do something with received job
    // ... send it using `mail()` function for example

    $job->markAsCompleted();
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

$result = $mailer->send(
    new MailMessage(json_decode($mailJob->getMessage(), true)),
    ['html' => __DIR__ . '/path/to/templates/mail.php'],
    ['variable' => 'Some testing variable passed to mail view']
);

if (!$result) {
    // ... cannot send email
    /* ... ack here with job not completed - will be set for later processing ... */
} else {
    $mailJob->markAsCompleted();
    /* ... ack here with job  completed - will be ack on the queue backend storage ... */
}
```
