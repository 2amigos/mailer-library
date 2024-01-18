<?php
namespace Da\Mailer\Test\Fixture;

use Da\Mailer\Enum\TransportType;
use Da\Mailer\Helper\ConfigReader;
use Da\Mailer\Model\MailMessage;
use Da\Mailer\Queue\Backend\Beanstalkd\BeanstalkdMailJob;
use Da\Mailer\Queue\Backend\Pdo\PdoMailJob;
use Da\Mailer\Queue\Backend\RabbitMq\RabbitMqJob;
use Da\Mailer\Queue\Backend\Redis\RedisMailJob;
use Da\Mailer\Queue\Backend\Sqs\SqsMailJob;

class FixtureHelper
{
    public static function getMailMessage()
    {
        return new MailMessage(self::getMailMessageSmtpConfigurationArray());
    }

    public static function getPdoMailJob()
    {
        return new PdoMailJob([
            'message' => json_encode(self::getMailMessage()),
            'timeToSend' => date('Y-m-d H:i:s', time()),
        ]);
    }

    public static function getRedisMailJob()
    {
        return new RedisMailJob([
            'message' => json_encode(self::getMailMessage()),
        ]);
    }

    public static function getBeanstalkdMailJob()
    {
        return new BeanstalkdMailJob([
            'message' => json_encode(self::getMailMessage()),
        ]);
    }

    public static function getSqsMailJob()
    {
        return new SqsMailJob([
            'message' => json_encode(self::getMailMessage()),
        ]);
    }

    public static function getRabbitMqJob()
    {
        return new RabbitMqJob([
            'message' => json_encode(self::getMailMessage()),
        ]);
    }

    public static function getMySqlConnectionConfiguration()
    {
        $config = ConfigReader::get();
        $pdo = $config['brokers']['pdo'];

        return [
            'dsn' => 'mysql:host=' . $pdo['host'] . ';dbname=mail_queue_test;port=' . $pdo['port'] ?: 3306,
            'username' => $pdo['username'],
            'password' => $pdo['password'] ?: '',
        ];
    }

    public static function getMailMessageSmtpConfigurationArray()
    {
        return [
            'transportOptions' => [],
            'transportType' => TransportType::SMTP,
            'host' => '127.0.0.1',
            'port' => 21,
            'from' => 'me@me.com',
            'to' => 'to@me.com',
            'cc' => 'cc@me.com',
            'bcc' => 'bcc@me.com',
            'subject' => 'subject',
            'bodyHtml' => '<b>This is body Html</b>',
            'bodyText' => 'This is body text',
            'attachments' => []
        ];
    }
}
