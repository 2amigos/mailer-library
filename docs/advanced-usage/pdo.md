# Pdo Backend Usage 

## Add the storage table to your database

 This backend requires that you create a table like the one specified in the `data` folder. It defaults to `mail_queue` 
 name but you can name it the way you want it as long as its structure remains the same and then you specify that 
 custom name on the adapter. 
 
 Currently we have a `mysql.sql` only, feel free to add your PDO compatible database to it. 
  
## Add email job to the queue 

```php
use Da\Mailer\Model\MailMessage;
use Da\Mailer\Queue\MailQueue;
use Da\Mailer\Queue\Backend\Pdo\PdoMailJob;
use Da\Mailer\Queue\Backend\Pdo\PdoQueueStoreAdapter;
use Da\Mailer\Queue\Backend\Pdo\PdoQueueStoreConnection;
use PDO;

$message = new MailMessage([
    'from' => 'sarah.connor@gmail.com',
    'to' => 'john.connor@gmail.com',
    'subject' => 'What is up?',
    'bodyText' => 'New mailing'
]);

$conn = new PdoQueueStoreConnection([
    'connectionString' => 'mysql:host=localhost;dbname=test',
    'username' => 'root',
    'password' => 'password'
], [PDO::ATTR_PERSISTENT => true]);

$adapter = new PdoQueueStoreAdapter($conn);

$queue = new MailQueue($adapter);

$job = new PdoMailJob([
    'message' => json_encode($message),
]);

if (!$queue->enqueue($job)) {
    // ... queue operation failed
}
```

## Fetch email job from queue

```php
use Da\Mailer\Queue\MailQueue;
use Da\Mailer\Queue\Backend\Pdo\PdoQueueStoreAdapter;
use Da\Mailer\Queue\Backend\Pdo\PdoQueueStoreConnection;

$conn = new PdoQueueStoreConnection([
    'connectionString' => 'mysql:host=localhost;dbname=test',
    'username' => 'root',
    'password' => 'password'
], [PDO::ATTR_PERSISTENT => true]);

$adapter = new PdoQueueStoreAdapter($conn);

$queue = new MailQueue($adapter);

if (($job = $queue->dequeue()) !== null) {
    // ... do something with received job
    // ... send it using `mail()` function for example 
    // ... or by using MailMessageWorker 

    $job->markAsCompleted();
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

$worker = new MailMessageWorker($mailer, $mailMessage);

// you could set the event handlers for `onFailure` or `onSuccess` here to do a different action according to the 
// results of the work
$worker->attach('onSuccess', $callableHere);
$worker->attach('onFailure', $anotherCallable);

$worker->run();

// this could be added in the 'onSuccess' event handler
$mailJob->markAsCompleted();
$queue->ack($job);
```
