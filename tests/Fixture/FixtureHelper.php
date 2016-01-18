<?php
namespace Da\Mailer\Test\Fixture;

use Da\Mailer\Model\MailMessage;
use Da\Mailer\Queue\Backend\Beanstalk\BeanstalkdMailJob;
use Da\Mailer\Queue\Backend\Pdo\PdoMailJob;
use Da\Mailer\Queue\Backend\Redis\RedisMailJob;
use Da\Mailer\Queue\Backend\Sqs\SqsMailJob;
use Da\Mailer\Transport\TransportInterface;

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

    public static function getMySqlConnectionConfiguration()
    {
        return [
            'connectionString' => 'mysql:host=127.0.0.1;dbname=mail_queue_test',
            'username' => 'root',
            'password' => '',
        ];
    }

    public static function getMailMessageSmtpConfigurationArray()
    {
        return [
            'transportOptions' => [],
            'transportType' => TransportInterface::TYPE_SMTP,
            'host' => '127.0.0.1',
            'port' => 21,
            'from' => 'me@me.com',
            'to' => 'to@me.com',
            'cc' => 'cc@me.com',
            'bcc' => 'bcc@me.com',
            'subject' => 'subject',
            'bodyHtml' => '<b>This is body Html</b>',
            'bodyText' => 'This is body text',
            'attachments' => [__DIR__ . '/../data/test_view.php'],
        ];
    }
}
