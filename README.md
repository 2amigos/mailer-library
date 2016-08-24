# Mailer 
[![Build Status](https://img.shields.io/travis/2amigos/mailer-library/master.svg?style=flat-square)](https://travis-ci.org/2amigos/mailer-library)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/2amigos/mailer-library/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/2amigos/mailer-library/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/2amigos/mailer-library/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/2amigos/mailer-library/?branch=master)

Many times we face the requirement to implement a queue mail functionality in our projects. There were queue and  
mailing libraries but there were none that could actually suit our needs and moreover, we always had to sync their 
functionality together. 

The `Mailer` library was built to fulfill the gaps that we have faced when implementing queue and/or mailing systems. It 
features: 

- message encryption/decryption just in case a mail message contains data that should not be publicly exposed. Perfect 
  for SAS systems. 
- queueing on different backends (currently supporting beanstalkd, pdo, redis and sqs) so we are not forced to use a 
  queue storage due to the narrowed capabilities of the framework and/or libraries
- unified system. Basic to Middle size projects do have mailing but they do not require another type of queue system.
  That's the reason the queue system is not standalone and is coupled with this system.


## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```bash
$ composer require 2amigos/google-places-library
```

or add

```
"2amigos/google-places-library": "*"
```

to the `require` section of your `composer.json` file.

## Usage 

- [Beanstalkd Backend](src/Queue/Backend/Beanstalkd/README.md)
- [Pdo Backend](src/Queue/Backend/Pdo/README.md)
- [RabbitMq Backend](src/Queue/Backend/RabbitMq/README.md)
- [Redis Backend](src/Queue/Backend/Redis/README.md)
- [SQS Backend](src/Queue/Backend/Sqs/README.md)

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

We have added a PHP code fixer to standardize our code. It includes Symfony, PSR2 and some contributors rules. 

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
