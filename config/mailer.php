<?php
if (defined('IS_TESTING') && IS_TESTING === true) {
    $envFile = '.env.testing';
} else {
    $envFile = '.env';
}
$env = Dotenv\Dotenv::createImmutable(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR, $envFile);
$env->load();

return [
    'config' => [
        'message_broker' => $_ENV['MESSAGE_BROKER'],
        'transport' => $_ENV['MAIL_TRANSPORT']
    ],

    'brokers' => [
        'redis' => [
            'host' => $_ENV['REDIS_HOST'],
            'port' => $_ENV['REDS_PORT'],
            'user' => $_ENV['REDIS_USER'],
            'password' => $_ENV['REDIS_PASSWORD']
        ],

        'sqs' => [
            'key' => $_ENV['SQS_KEY'],
            'secret' => $_ENV['SQS_SECRET'],
            'region' => $_ENV['SQS_REGION']
        ],

        'beanstalkd' => [
            'host' => $_ENV['BEANSTALKD_HOST'],
            'port' => $_ENV['BEANSTALKD_PORT']
        ],

        'pdo' => [
            'username' => $_ENV['PDO_USER'],
            'password' => $_ENV['PDO_PASSWORD'],
            'host' => $_ENV['PDO_HOST'],
            'port' => $_ENV['PDO_PORT'] ?: 3306,
            'db' => $_ENV['PDO_DBNAME']
        ],

        'rabbitmq' => [
            'host' => $_ENV['RABBITMQ_HOST'],
            'port' => $_ENV['RABBITMQ_PORT'],
            'user' => $_ENV['RABBITMQ_USER'],
            'password' => $_ENV['RABBITMQ_PASSWORD']
        ]
    ],

    'transports' => [
        'smtp' => [
            'host' => $_ENV['MAILER_SMTP_HOST'],
            'port' => $_ENV['MAILER_SMTP_PORT'],
            'options' => [
                'username' => $_ENV['MAILER_SMTP_USER'],
                'password' => $_ENV['MAILER_SMTP_PASSWORD'],
                'tls' => $_ENV['MAILER_SMTP_TLS']
            ]
        ],
        'sendMail' => [
            'dsn' => $_ENV['SENDMAIL_DSN']
        ],
        'mail' => [
            'dsn' => $_ENV['MAIL_DSN']
        ]
    ],
    'mail-charset' => $_ENV['MAIL_CHARSET'] ?: 'utf-8'
];
