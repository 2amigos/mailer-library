<?php
namespace Da\Mailer\Test;

use Da\Mailer\Enum\TransportType;
use Da\Mailer\Mailer;
use Da\Mailer\Model\MailMessage;
use Da\Mailer\Test\Fixture\FixtureHelper;
use Da\Mailer\Transport\MailTransport;
use Da\Mailer\Transport\SendMailTransport;
use Da\Mailer\Transport\SmtpTransport;
use Da\Mailer\Transport\SmtpTransportFactory;
use Da\Mailer\Transport\TransportFactory;
use Mockery;
use PHPUnit\Framework\TestCase;
use Da\Mailer\Builder\MailerBuilder;
use Symfony\Component\Mailer\Transport\TransportInterface;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class MailerTest extends TestCase
{
    public function testFromMailMessageMethod()
    {
        $mailMessage = FixtureHelper::getMailMessage();
        $options = [
            'host' => $mailMessage->host,
            'port' => $mailMessage->port,
            'options' => $mailMessage->transportOptions,
        ];

        Mockery::mock('alias:' . TransportFactory::class)
            ->shouldReceive('create')
            ->once()
            ->withArgs([$options, $mailMessage->transportType])
            ->andReturnUsing(
                function ($options) {
                    return new SmtpTransportFactory($options);
                }
            );

        $mailer = Mailer::fromMailMessage($mailMessage);

        $this->assertTrue($mailer instanceof Mailer);
        $this->assertTrue($mailer->getTransport() instanceof SmtpTransport);
    }

    public function testConstructionOptions()
    {
        $mailMessage = FixtureHelper::getMailMessage();
        // TODO Update to use Native instead.
        $mailer = MailerBuilder::make(TransportType::MAIL);

        $this->assertTrue($mailer->getTransport() instanceof MailTransport);
        $this->assertTrue($mailer->getTransportInstance() instanceof TransportInterface);
        $this->assertTrue($mailer->getLog() === null);

        $sendMailTransport = MailerBuilder::make(TransportType::SEND_MAIL);

        $this->assertTrue($sendMailTransport->getTransport() instanceof SendMailTransport);
        $this->assertTrue($sendMailTransport->getTransportInstance() instanceof TransportInterface);
    }

    public function testSetTransport()
    {
        $mailer = MailerBuilder::make(TransportType::SMTP);

        $mailer2 = MailerBuilder::make(TransportType::MAIL);

        $mailer->setTransport($mailer2->getTransport());

        $this->assertInstanceOf(MailTransport::class, $mailer->getTransport());
    }

    public function testSend()
    {
        $message = MailMessage::make([
            'from' => 'from@me.com',
            'to' => 'to@me.com',
            'subject' => 'mailing test',
            'bodyHtml' => 'whats up?',
        ]);

        $smtpTransport = (new SmtpTransportFactory([
            'host' => '',
            'port' => '',
            'username' => '',
            'password' => ''
        ]))->create();

        /** @var Mailer $mailer */
        $mailer = Mockery::mock(Mailer::class, [$smtpTransport])
            ->shouldReceive('send')
            ->with($message)
            ->getMock();

        //TODO enhance this test on future
        $s = $mailer->send($message);

        $this->assertNull($s);
    }

    public function testTransportException()
    {
        $this->expectException(\Symfony\Component\Mailer\Exception\TransportException::class);

        MailerBuilder::make()->send(new MailMessage([
            'from' => 'from@me.com',
            'to' => 'to@me.com',
            'subject' => 'mailing test',
            'bodyHtml' => 'whats up?',
        ]));
    }
}
