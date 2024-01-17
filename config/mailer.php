<?php
if (defined('IS_TESTING') && IS_TESTING === true) {
    $envFile = '.env.testing';
} else {
    $envFile = '.env';
}

$env = Dotenv\Dotenv::createUnsafeImmutable(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR, $envFile);
$env->load();

return [
    'config' => [
        'message_broker' => getenv('MESSAGE_BROKER'),
        'transport' => getenv('MAIL_TRANSPORT'),
    ],

    'brokers' => [
        'redis' => [
            'host' => getenv('REDIS_HOST'),
            'port' => getenv('REDS_PORT'),
            'user' => getenv('REDIS_USER'),
            'password' => getenv('REDIS_PASSWORD')
        ],

        'sqs' => [
            'key' => getenv('SQS_KEY'),
            'secret' => getenv('SQS_SECRET'),
            'region' => getenv('SQS_REGION')
        ],

        'beanstalkd' => [
            'host' => getenv('BEANSTALKD_HOST'),
            'port' => getenv('BEANSTALKD_PORT')
        ],

        'pdo' => [
            'user' => getenv('PDO_USER'),
            'password' => getenv('PDO_PASSWORD'),
            'host' => getenv('PDO_HOST'),
            'port' => getenv('PDO_PORT')
        ],

        'rabbitmq' => [
            'host' => getenv('RABBITMQ_HOST'),
            'port' => getenv('RABBITMQ_PORT'),
            'user' => getenv('RABBITMQ_USER'),
            'password' => getenv('RABBITMQ_PASSWORD'),
        ]
    ],

    'transports' => [
        'smtp' => [
            'host' => getenv('MAILER_SMTP_HOST'),
            'port' => getenv('MAILER_SMTP_PORT'),
            'options' => [
                'username' => getenv('MAILER_SMTP_USER'),
                'password' => getenv('MAILER_SMTP_PASSWORD'),
                'tls' => getEnv('MAILSER_SMTP_TLS')
            ]
        ],
        'sendMail' => [
            'dsn' => getenv('SENDMAIL_DSN')
        ],
        'mail' => [
            'dsn' => getenv('MAIL_DSN')
        ]
    ],
    'mail-charset' => getenv('MAIL_CHARSET') ?? 'utf-8',
];
