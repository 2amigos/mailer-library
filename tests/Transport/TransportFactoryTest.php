<?php
namespace Da\Mailer\Test\Transport;

use Da\Mailer\Transport\MailTransport;
use Da\Mailer\Transport\MailTransportFactory;
use Da\Mailer\Transport\SendMailTransport;
use Da\Mailer\Transport\SendMailTransportFactory;
use Da\Mailer\Transport\SmtpTransport;
use Da\Mailer\Transport\SmtpTransportFactory;
use Da\Mailer\Transport\TransportFactory;
use Da\Mailer\Transport\TransportInterface;
use PHPUnit_Framework_TestCase;

class TransportFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testCreateTransport()
    {
        $smtpConfig = [
            'host' => 'localhost',
            'port' => 587,
            'options' => [
                'username' => 'Obiwoan',
                'password' => 'Kenovi',
                'encryption' => 'ssl',
                'authMode' => 'Plain',
            ],
        ];
        $mailConfig = ['options' => '-f%s'];
        $sendMailConfig = ['options' => '/usr/sbin/sendmail -s'];

        $smtpFactory = TransportFactory::create($smtpConfig, TransportInterface::TYPE_SMTP);

        $this->assertTrue($smtpFactory instanceof SmtpTransportFactory);

        $smtp = $smtpFactory->create();

        $this->assertTrue($smtp instanceof SmtpTransport);

        /**
         * @var \Swift_SmtpTransport
         */
        $swift = $smtp->getSwiftTransportInstance();

        $this->assertEquals($smtpConfig['host'], $swift->getHost());
        $this->assertEquals($smtpConfig['port'], $swift->getPort());
        $this->assertEquals($smtpConfig['options']['username'], $swift->getUsername());
        $this->assertEquals($smtpConfig['options']['password'], $swift->getPassword());
        $this->assertEquals($smtpConfig['options']['encryption'], $swift->getEncryption());
        $this->assertEquals($smtpConfig['options']['authMode'], $swift->getAuthMode());

        $mailFactory = TransportFactory::create($mailConfig, TransportInterface::TYPE_MAIL);

        $this->assertTrue($mailFactory instanceof MailTransportFactory);

        $mail = $mailFactory->create();

        $this->assertTrue($mail instanceof MailTransport);

        /**
         * @var \Swift_MailTransport
         */
        $swift = $mail->getSwiftTransportInstance();

        $this->assertEquals($mailConfig['options'], $swift->getExtraParams());

        $sendMailFactory = TransportFactory::create($sendMailConfig, TransportInterface::TYPE_SEND_MAIL);

        $this->assertTrue($sendMailFactory instanceof SendMailTransportFactory);

        $sendMail = $sendMailFactory->create();

        $this->assertTrue($sendMail instanceof SendMailTransport);
        /**
         * @var \Swift_SendMailTransport
         */
        $swift = $sendMail->getSwiftTransportInstance();

        $this->assertEquals($sendMailConfig['options'], $swift->getCommand());
    }

    public function testDefaultParameters()
    {
        $mail = (new MailTransportFactory([]))->create();
        $sendMail = (new SendMailTransportFactory([]))->create();

        /**
         * @var \Swift_MailTransport
         */
        $swift = $mail->getSwiftTransportInstance();

        $this->assertEquals('-f%s', $swift->getExtraParams());

        /**
         * @var \Swift_SendMailTransport
         */
        $swift = $sendMail->getSwiftTransportInstance();

        $this->assertEquals('/usr/sbin/sendmail -bs', $swift->getCommand());
    }

    /**
     * @expectedException \Da\Mailer\Exception\InvalidTransportTypeArgumentException
     */
    public function testInvalidTransportTypeArgumentException()
    {
        $transport = TransportFactory::create([], 'starWars');
    }
}
