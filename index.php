<?php
require_once "./vendor/autoload.php";

$message = \Da\Mailer\Model\MailMessage::make([
    'from' => \Da\Mailer\Mail\Dto\EmailAddress::make('contato@newenglishbr.com', 'Contato 23'),
    'to' => \Da\Mailer\Mail\Dto\EmailAddress::make('jonatas094@gmail.com', 'J. Souza'),
    'subject' => 'What is up?',
    'bodyHtml' =>  __DIR__ . DIRECTORY_SEPARATOR . 'test.html',
]);

\Da\Mailer\Builder\MailerBuilder::make()->send($message);
die;
$queue = \Da\Mailer\Builder\QueueBuilder::make();
while(! $queue->isEmpty()) {
    var_dump($job = $queue->dequeue());
    $job->markAsCompleted();
    $queue->ack($job);
}

die;
#$mailer = Da\Mailer\Builder\MailerBuilder::build();
$message = new \Da\Mailer\Model\MailMessage([
    'from' => 'contato@newenglishbr.com',
    'to' => 'jonatas094@gmail.com',
    'subject' => 'What is up?',
]);
var_dump($message);
die;
$mailer->send($message, ['html' => './test.html']);
var_dump($mailer->getLog());
die;
$message = new \Da\Mailer\Model\MailMessage([
    'from' => 'contato@newenglishbr.com',
    'to' => 'jonatas094@gmail.com',
    'subject' => 'What is up?',
]);

$conn = new \Da\Mailer\Queue\Backend\RabbitMq\RabbitMqQueueConnection([
    'host' => 'localhost',
    'port' => 5672,
    'user' => 'guest',
    'password' => 'guest',
]);

$adapter = new \Da\Mailer\Queue\Backend\RabbitMq\RabbitMqQueueStoreAdapter($conn);

$queue = new \Da\Mailer\Queue\MailQueue($adapter);

$job = new \Da\Mailer\Queue\Backend\RabbitMq\RabbitMqJob([
    'message' => json_encode($message),
]);

if (!$queue->enqueue($job)) {
    throw new \Exception('job failed');
}
#var_dump($queue->isEmpty());
#die;
$mailJob = $queue->dequeue();
#$mailJob->markAsCompleted();
$queue->ack($mailJob);
var_dump($mailJob->getDeliveryTag());
var_dump($queue->isEmpty());
