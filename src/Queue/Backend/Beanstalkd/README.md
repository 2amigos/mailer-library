# Beanstalkd Backend Usage 

This assumes you have beanstalkd running on your computer and on port 11300.

## Add email job to the queue 

```php 
use Da\Mailer\Model\MailMessage;
use Da\Mailer\Queue\MailQueue;
use Da\Mailer\Queue\Backend\Beanstalkd\BeanstalkdMailJob;
use Da\Mailer\Queue\Backend\Beanstalkd\BeanstalkdQueueStoreAdapter;
use Da\Mailer\Queue\Backend\Beanstalkd\BeanstalkdQueueStoreConnection;
use PDO;

$message = new MailMessage([
    'from' => 'sarah.connor@gmail.com',
    'to' => 'john.connor@gmail.com',
    'subject' => 'What is up?',
]);

$conn = new BeanstalkdQueueStoreConnection([
    'host' => 'localhost',
    'port' => 'root']);

$adapter = new BeanstalkdQueueStoreAdapter($conn);

$queue = new MailQueue($adapter);

$job = new BeanstalkdMailJob([
    'message' => json_encode($message),
]);

if (!$queue->enqueue($job)) {
    // ... queue operation failed
}
```

## Fetch email job from queue

```php
use Da\Mailer\Queue\MailQueue;
use Da\Mailer\Queue\Backend\Beanstalkd\BeanstalkdQueueStoreAdapter;
use Da\Mailer\Queue\Backend\Beanstalkd\BeanstalkdQueueStoreConnection;

$conn = new BeanstalkdQueueStoreConnection([
    'connectionString' => 'mysql:host=localhost;dbname=test',
    'username' => 'root',
    'password' => 'password'
], [PDO::ATTR_PERSISTENT => true]);

$adapter = new BeanstalkdQueueStoreAdapter($conn);

$queue = new MailQueue($adapter);

if (($job = $queue->dequeue()) !== null) {
    // ... do something with received job
    // ... send it using `mail()` function for example 
    // ... or by using MailMessageWorker 

    $job->setDeleted(true);
    $queue->ack($job);
}
```

## Send email job with `MailMessageWorker` 

This could be an example using `MailMessageWorker` and a `MailJob` with specific transport configurations in it. Very 
useful if the mail jobs in the queue do belong to your customers and they can specify their SMTP configuration details 
for sending emails. 
 

```php
use Da\Mailer\Mailer;
use Da\Mailer\Model\MailMessage;
use Da\Mailer\Transport\TransportFactory
use Da\Mailer\Queue\Cli\MailMessageWorker;

$mailJob = /* ... get mail job here (see above) ... */;
$mailMessage = json_decode($mailJob->getMessage()); /* ... if you have json encoded ... */
$transport = TransportFactory::create($mailMessage->transportOptions, $mailMessage->transportType);
$mailer = new Mailer($transport);

$worker = new MailMessageWorker($transport, $mailMessage);

// you could set the event handlers for `onFailure` or `onSuccess` here to do a different action according to the 
// results of the work
$worker->attach('onSuccess', $callableHere);
$worker->attach('onFailure', $anotherCallable);

$worker->run();
```
