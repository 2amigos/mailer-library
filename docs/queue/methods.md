## Methods

enqueue
---
The enqueue method takes a object from `\Da\Mailer\Model\MailJob` class as 
parameter. It pushes the object to the end of the queue.

```php
$message = new \Da\Mailer\Model\MailMessage([
    'from' => 'sarah.connor@gmail.com',
    'to' => 'john.connor@gmail.com',
    'subject' => 'What is up?',
    'textBody' => 'I hope to find you well...'
]);

$queue = \Da\Mailer\Queue\MailQueue::make();
$mailJob = \Da\Mailer\Builder\MailJobBuilder::make([
    'message' => $message
]);

if (! $queue->enqueue($mailJob)) {
    // something wrong happened
}
```

dequeue
---
The dequeue method fetches the very next job on the queue.

```php
$mailJob = \Da\Mailer\Queue\MailQueue::make()->dequeue();
var_dump($mailJob);

// output:
// class Da\Mailer\Queue\Backend\RabbitMq\RabbitMqJob#32 (5) {
//  private $deliveryTag =>
//  ...
```

ack
---
The ack method takes an object from the `\Da\Mailer\Model\MailJob` class. It is responsible to inform the broker about a message status.
If the message is full processed and completed (literally have to MailJob with the property `isCompleted` as true. You can check how to assess it [here](../../README.md#mailjob)), the broker will remove it from new rounds,
otherwise, it'll requeue it.

```php
$queue->ack($mailJob);
```

isEmpty()
---
Return true if there is no messages in the queue, otherwise, return false.

```php
$queue->isEmpty();
// false
```
