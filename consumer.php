<?php
require_once "./vendor/autoload.php";

$conn = new \Da\Mailer\Queue\Backend\Redis\RedisQueueStoreConnection([
    'host' => 'localhost',
    'port' => 6379,
]);

$adapter = new \Da\Mailer\Queue\Backend\Redis\RedisQueueStoreAdapter($conn);
$queue = new \Da\Mailer\Queue\MailQueue($adapter);
$transport = new \Da\Mailer\Transport\SmtpTransport('smtp.umbler.com','587',[
        'username' => 'contato@newenglishbr.com',
        'password' => 't8o_3FOBW6t!'
    ]);
$mailer = new \Da\Mailer\Mailer($transport);

if (($mailJob = $queue->dequeue()) !== null) {

    echo "sending...";
    #$mailJob->markAsCompleted();
    #$queue->ack($mailJob);
    #die;
    // ... do something with received job
    // ... send it using `mail()` function for example
    #var_dump($job);
    $result = $mailer->send(
        new \Da\Mailer\Model\MailMessage(json_decode($mailJob->getMessage(), true)),
        ['html' => __DIR__ . '/test.html']
    );

    if (!empty($result)) {
        // ... cannot send email
        /* ... ack here with job not completed - will be set for later processing ... */
        $mailJob->markAsCompleted();
        $queue->ack($mailJob);

        echo "could not send the email";
    } else {
        $mailJob->markAsCompleted();
        $queue->ack($mailJob);
        /* ... ack here with job  completed - will be ack on the queue backend storage ... */
        echo "all good";
    }
}
