<?php
namespace Da\Mailer\Test\Model;

use Da\Mailer\Model\MailMessage;
use Da\Mailer\Test\Fixture\FixtureHelper;
use PHPUnit\Framework\TestCase;
use Swift_Message;

class MailMessageTest extends TestCase
{
    public function testMailMessageMagicMethods()
    {
        $config = FixtureHelper::getMailMessageSmtpConfigurationArray();
        $mailMessage = FixtureHelper::getMailMessage();

        foreach ($config as $attribute => $value) {
            $this->assertEquals($config[$attribute], $mailMessage->$attribute);
            $this->assertTrue(isset($mailMessage->$attribute));
            unset($mailMessage->$attribute);
            $this->assertTrue(isset($mailMessage->$attribute) === false);
        }
    }

    public function testMailMessageJsonSerializeAndFromArrayMethods()
    {
        $config = FixtureHelper::getMailMessageSmtpConfigurationArray();
        $mailMessage = FixtureHelper::getMailMessage();

        $json = json_encode($mailMessage, JSON_NUMERIC_CHECK);
        $this->assertEquals(json_encode($config, JSON_NUMERIC_CHECK), $json);
        $decodedMailMessage = MailMessage::fromArray(json_decode($json, true));

        $this->assertEquals($mailMessage, $decodedMailMessage);
    }
}
