<?php
namespace Da\Mailer\Test\Transport;

use Da\Mailer\Enum\TransportType;
use Da\Mailer\Transport\MailTransport;
use Da\Mailer\Transport\MailTransportFactory;
use Da\Mailer\Transport\SendMailTransport;
use Da\Mailer\Transport\SendMailTransportFactory;
use Da\Mailer\Transport\SmtpTransport;
use Da\Mailer\Transport\SmtpTransportFactory;
use Da\Mailer\Transport\TransportFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

class TransportFactoryTest extends TestCase
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
        $mailConfig = ['dsn' => 'null://null'];
        $sendMailConfig = ['dsn' => 'null://null'];

        $smtpFactory = TransportFactory::create($smtpConfig, TransportType::SMTP);

        $this->assertTrue($smtpFactory instanceof SmtpTransportFactory);

        $smtp = $smtpFactory->create();

        $this->assertTrue($smtp instanceof SmtpTransport);

        /** @var EsmtpTransport $transport */
        $transport = $smtp->getInstance();

        $this->assertEquals($smtpConfig['options']['username'], $transport->getUsername());
        $this->assertEquals($smtpConfig['options']['password'], $transport->getPassword());

        $mailFactory = TransportFactory::create($mailConfig, TransportType::MAIL);

        $this->assertTrue($mailFactory instanceof MailTransportFactory);

        $mail = $mailFactory->create();

        $this->assertTrue($mail instanceof MailTransport);

        $sendMailFactory = TransportFactory::create($sendMailConfig, TransportType::SEND_MAIL);

        $this->assertTrue($sendMailFactory instanceof SendMailTransportFactory);

        $sendMail = $sendMailFactory->create();

        $this->assertTrue($sendMail instanceof SendMailTransport);
    }

    public function testInvalidTransportTypeArgumentException()
    {
        $this->expectException(\Da\Mailer\Exception\InvalidTransportTypeArgumentException::class);

        TransportFactory::create([], 'starWars');
    }
}
