# RabbitMq Backend Usage 

## Add an Email to the Queue

```php
$message = new MailMessage([
    'from' => 'sarah.connor@gmail.com',
    'to' => 'john.connor@gmail.com',
    'subject' => 'What is up?',
    'bodyText' => 'New mailing'
]);

$connection = new \Da\Mailer\Queue\Backend\RabbitMq\RabbitMqQueueConnection([
    'host' => 'localhost',
    'port' => 5672,
    'user' => 'guest',
    'password' => 'guest'
]);

$adapter = new \Da\Mailer\Queue\Backend\RabbitMq\RabbitMqQueueStoreAdapter($connection);
$queue = new \Da\Mailer\Queue\MailQueue($adapter);

$job = new \Da\Mailer\Queue\Backend\RabbitMq\RabbitMqJob([
    'message' => json_encode($message);
]);

if (! $queue->enqueue($job)) {
    //something wrong happened
}
```

## Fetch email from the queue

```php
$connection = new \Da\Mailer\Queue\Backend\RabbitMq\RabbitMqQueueConnection([
    'host' => 'localhost',
    'port' => 5672,
    'user' => 'guest',
    'password' => 'guest'
]);

$adapter = new \Da\Mailer\Queue\Backend\RabbitMq\RabbitMqQueueStoreAdapter($connection);
$queue = new \Da\Mailer\Queue\MailQueue($adapter);

$job = $queue->dequeue();
if ($job !== null) {
    // perform some action with the job e.g send email
    $job->markAsCompleted();
    $queue->ack($job);
}
```

## Send email with the mail() function

```php
$transport = new \Da\Mailer\Transport\SmtpTransport($host, $user, $options);
$mailer = new \Da\Mailer\Model\MailMessage($transport);

$mailJob = /* ... get mail job here ... */;

$status = null;

try {
    $status = $mailer->send(
        new MailMessage(json_decode($mailJob->getMessage(), true))
    );
} catch(Exception $e) {
    // log exception;
}

if (is_null($status)) {
    // ... cannot send email
    /* ... ack here with job not completed - will be set for later processing ... */
} else {
    $mailJob->markAsCompleted();
    /* ... ack here with job  completed - will be ack on the queue backend storage ... */
}
```
