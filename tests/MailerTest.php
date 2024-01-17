<?php
namespace Da\Mailer\Test;

use Da\Mailer\Enum\TransportType;
use Da\Mailer\Mailer;
use Da\Mailer\Test\Fixture\FixtureHelper;
use Da\Mailer\Transport\MailTransport;
use Da\Mailer\Transport\SendMailTransport;
use Da\Mailer\Transport\SmtpTransport;
use Da\Mailer\Transport\SmtpTransportFactory;
use Da\Mailer\Transport\TransportFactory;
use Mockery;
use PHPUnit\Framework\TestCase;
use Da\Mailer\Builder\MailerBuilder;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
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

        /** TODO Properly mock emails sent*/
        /*$this->assertTrue(
            $mailer->send(
                $mailMessage,
                ['text' => __DIR__ . '/data/test_view.php'],
                ['force' => 'force', 'with' => 'with', 'you' => 'you']
            ) instanceof SentMessage
        );*/

        #$this->assertEquals(1, $mailer->send($mailMessage));
    }

    public function testSendMailer()
    {
        $this->assertTrue(true);
        #$this->markTestSkipped('TODO::properly mock Transport Interface to fake emails');

        /*$mailMessage = FixtureHelper::getMailMessage();

        $mailer = MailerBuilder::make();
        date_default_timezone_set('UTC');
        $this->assertEquals(null, $mailer->send($mailMessage));*/
    }

    public function testSetTransport()
    {
        $mailer = MailerBuilder::make(TransportType::SMTP);

        $mailer2 = MailerBuilder::make(TransportType::MAIL);

        $mailer->setTransport($mailer2->getTransport());

        $this->assertInstanceOf(MailTransport::class, $mailer->getTransport());
    }
}
