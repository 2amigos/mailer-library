# Mailer 

[![tests](https://github.com/2amigos/mailer-library/actions/workflows/ci.yml/badge.svg)](https://github.com/2amigos/mailer-library/actions/workflows/ci.yml)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/d0e8d6e968944592a95a0911d8f178ff)](https://app.codacy.com/gh/2amigos/mailer-library/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)
[![Codacy Badge](https://app.codacy.com/project/badge/Coverage/d0e8d6e968944592a95a0911d8f178ff)](https://app.codacy.com/gh/2amigos/mailer-library/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_coverage)
[![Latest Stable Version](http://poser.pugx.org/2amigos/mailer/v)](https://packagist.org/packages/2amigos/mailer)
[![Total Downloads](http://poser.pugx.org/2amigos/mailer/downloads)](https://packagist.org/packages/2amigos/mailer)
[![PHP Version Require](http://poser.pugx.org/2amigos/mailer/require/php)](https://packagist.org/packages/2amigos/mailer)

Many times we face a requirement to implement queue mail functionality in our projects. There are queue and  
mailing libraries, but there seemed to be none that could actually suit our needs and moreover, we always had to sync their 
functionality together. 

The `Mailer` library was built to fill the gaps that we have faced when implementing queue and/or mailing systems. It 
features: 

- message encryption/decryption just in case a mail message contains data that should not be publicly exposed. Perfect 
  for SAS systems. 
- queueing on different backends (currently supporting beanstalkd, pdo, redis, sqs and rabbitmq) so we are not forced to use a 
  queue storage due to the narrowed capabilities of the framework and/or libraries
- unified system. Basic to Middle size projects do have mailing but they do not require another type of queue system.
  That's the reason the queue system is not standalone and is coupled with this system.


## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```bash
$ composer require 2amigos/mailer
```

or add

```
"2amigos/mailer": "^2.0"
```

to the `require` section of your `composer.json` file.

Usage
---

## Configuration

All the configuration needed to set up the message broker connections as
the mailer transport should be performed on the .env file. A .env.example file 
is provided, you can just copy it and start over! 

```bash
$ cp .evn.example .env
```

The keys `MESSAGE_BROKER` and `MAIL_TRANSPORT` defines the default message broker
and mail transport, and they are mandatory to be filled. By default, its
set to use Redis broker with SMTP transport.

You can access the related configuration values by calling: 
```php
$values = \Da\Mailer\Helper\ConfigReader::get(); // array
```

## Mail Messages

The `MailMessage` class is an abstraction for an email 
content. Beside the attachments, you can specify the email content
directly by the constructor or directly accessor.

```php
$message = new \Da\Mailer\Model\MailMessage([
    'from' => 'sarah.connor@gmail.com', 
    'to' => 'john.connor@gmail.com',
    'subject' => 'What is up?',
    'textBody' => 'I hope to find you well...'
]);

// or
$message->bodyHtml = "I hope I'm finding doing <b>well.</b>"
// body html takes priority over body text with both were set.
```

You can also use our `EmailAddress` class to define emails with related name:

```php
$message->cc = [
    \Da\Mailer\Mail\Dto\EmailAddress::make('Samn@email.com', 'Samantha');
    \Da\Mailer\Mail\Dto\EmailAddress::make('oliver@email.com', 'Oliver');
];
```

And to add attachments, you can make use of the method `addAttachment(path, name)`:

```php
$message->addAttachment(__DIR__ . DIRECTORY_SEPARATOR . 'file-test.pdf', 'Important File.png');
```

Also, you can set text or html body as a resource path.

```php
$message->bodyHtml = __DIR__ . DIRECTORY_SEPARATOR . 'html-template.html';
```

### Available public properties:
| Property |     Type      |
|:--------:|:-------------:|
|   from   | string, array |
|    to    | string, array |
|    cc    | string, array |
|   bcc    | string, array |
| subject  | string |
| bodyText | string |
| bodyHtml | string |

### enqueue MailMessage

You can easily assess the message enqueue by calling the method `enqueue`.
```php
$message->enqueue();
```
The message will enqueued to the default message broker, and use the default 
transport.

## MailJob

The MailJob class will abstract the message behavior for our queue application.
You can create a new MailJob with the `MailJobBuilder` class:

```php
$mailJob = \Da\Mailer\Builder\MailJobBuilder::make([
    'message' => json_encode($message)
]);
```

Behind the hoods, the builder will build the MailJob specialized to the
default broker you've defined on your .env file. If you ever want a
mail job to be created to a different broker than your default, you
can set it as the second argument, using one value 
from the `\Da\Mailer\Enum\MessageBrokerEnum` enum:

```php
$mailJob = \Da\Mailer\Builder\MailJobBuilder::make([
        'message' => json_encode($message)
    ],
    \Da\Mailer\Enum\MessageBrokerEnum::BROKER_SQS
);
```

The MailJob class has a set of methods to manipulate it's content
and also to check its status. The next piece of code cover them all:

```php
$mailJob->getMessage(); // returns the MailJob message
$mailJob->markAsCompleted(); // void, mark the job as completed
$mailJob->isCompleted(); // returns true if the job has been complete
$mailJob->setMessage(new \Da\Mailer\Model\MailMessage()); // change the job's message 
```

## Mailer
The Mailer class is the one we use for sending the emails.

```php
$message = new \Da\Mailer\Model\MailMessage([
    'from' => 'sarah.connor@gmail.com', 
    'to' => 'john.connor@gmail.com',
    'subject' => 'What is up?',
    'textBody' => 'I hope to find you well...'
]);

$mailer = \Da\Mailer\Builder\MailerBuilder::make();
// or if you want to set a transport different from the default
$mailer = \Da\Mailer\Builder\MailerBuilder::make(\Da\Mailer\Enum\TransportType::SEND_MAIL);
$mailer->send($message); // returns \Symfony\Component\Mailer\SentMessage::class|null
```

## Queues

To create a queue, you can make use of our `QueueBuilder` class. It will return 
a queue object with a few methods to handle the queue. They are:

- [enqueue(MailJob $job)](docs/queue/methods.md#enqueue): bool
- [dequeue()](docs/queue/methods.md#dequeue): mailjob
- [ack(MailJob $job)](docs/queue/methods.md#ack): void
- [isEmpty()](docs/queue/methods.md#isempty): bool

```php
$queue = \Da\Mailer\Builder\QueueBuilder::make();

// if you want to use a different broker than the default
$queue = \Da\Mailer\Builder\QueueBuilder::make(\Da\Mailer\Enum\MessageBrokerEnum::BROKER_RABBITMQ);
```

## Advanced usage

If you want to handle your message broker and smtp manually, you can follow
through the following topics:

- [Beanstalkd Backend](docs/advanced-usage/bt.md)
- [Pdo Backend](docs/advanced-usage/pdo.md)
- [RabbitMq Backend](docs/advanced-usage/rabbitmq.md)
- [Redis Backend](docs/advanced-usage/redis.md)
- [SQS Backend](docs/advanced-usage/sqs.md)

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Clean code

We have added some development tools for you to contribute to the library with clean code: 

- PHP mess detector: Takes a given PHP source code base and look for several potential problems within that source.
- PHP code sniffer: Tokenizes PHP, JavaScript and CSS files and detects violations of a defined set of coding standards.
- PHP code fixer: Analyzes some PHP source code and tries to fix coding standards issues.

And you should use them in that order. 

### Using php mess detector

Sample with all options available:

```bash 
 ./vendor/bin/phpmd ./src text codesize,unusedcode,naming,design,controversial,cleancode
```

### Using code sniffer
 
```bash 
 ./vendor/bin/phpcs -s --report=source --standard=PSR2 ./src
```

### Using code fixer

We have added a PHP code fixer to standardize our code. It includes Symfony, [PSR-12](https://www.php-fig.org/psr/psr-12/) and some contributors rules. 

```bash 
./vendor/bin/php-cs-fixer --config-file=.php_cs fix ./src
```

## Testing

 ```bash
 $ ./vendor/bin/phpunit
 ```


## Credits

- [Antonio Ramirez](https://github.com/tonydspaniard)
- [All Contributors](https://github.com/2amigos/mailer-library/graphs/contributors)

## License

The BSD License (BSD). Please see [License File](LICENSE.md) for more information.

<blockquote>
    <a href="http://www.2amigos.us"><img src="http://www.gravatar.com/avatar/55363394d72945ff7ed312556ec041e0.png"></a><br>
    <i>web development has never been so fun</i><br> 
    <a href="http://www.2amigos.us">www.2amigos.us</a>
</blockquote>
