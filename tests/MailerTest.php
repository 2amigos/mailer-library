<?php
namespace Da\Mailer\Test;

use Da\Mailer\Mailer;
use Da\Mailer\Transport\MailTransport;
use Da\Mailer\Transport\MailTransportFactory;
use Da\Mailer\Transport\SendMailTransport;
use Da\Mailer\Transport\SendMailTransportFactory;
use Da\Mailer\Transport\SmtpTransport;
use Da\Mailer\Transport\SmtpTransportFactory;
use Da\Mailer\Transport\TransportFactory;
use Da\Mailer\Test\Fixture\FixtureHelper;
use Mockery;
use PHPUnit_Framework_TestCase;
use Swift_Events_CommandEvent;
use Swift_Mailer;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class MailerTest extends PHPUnit_Framework_TestCase
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
        $mailTransport = (new MailTransportFactory([]))->create();
        $mailer = new Mailer($mailTransport, true);

        $this->assertTrue($mailer->getTransport() instanceof MailTransport);
        $this->assertTrue($mailer->getSwiftMailerInstance() instanceof Swift_Mailer);
        $this->assertTrue($mailer->getLog() === null);

        $sendMailTransport = (new SendMailTransportFactory([]))->create();
        $mailer->updateTransport($sendMailTransport);

        $this->assertTrue($mailer->getTransport() instanceof SendMailTransport);
        $this->assertTrue($mailer->getSwiftMailerInstance() instanceof Swift_Mailer);

        $plugin = new TestSwiftPlugin();
        $this->assertSame($mailer, $mailer->addPlugin($plugin));
        $this->assertSame($mailer, $mailer->registerPlugins());
        $this->assertEquals("", $mailer->getLog());
        // is dry run, should be fine sending as it will return number of message sent

        $this->assertEquals(
            1,
            $mailer->send(
                $mailMessage,
                ['text' => __DIR__ . '/data/test_view.php'],
                ['force' => 'force', 'with' => 'with', 'you' => 'you']
            )
        );
        $this->assertEquals(1, $mailer->sendSwiftMessage($mailMessage->asSwiftMessage()));
    }

    public function testSendSwiftMailer()
    {
        $mailMessage = FixtureHelper::getMailMessage()->asSwiftMessage();
        Mockery::mock('overload:Swift_Mailer')
            ->shouldIgnoreMissing()
            ->shouldReceive('send')
            ->withAnyArgs()
            ->once();
        $mailTransport = (new MailTransportFactory([]))->create();
        $mailer = new Mailer($mailTransport);
        date_default_timezone_set('UTC');
        $this->assertEquals(null, $mailer->sendSwiftMessage($mailMessage));
    }
}

class TestSwiftPlugin implements \Swift_Events_CommandListener
{
    public function commandSent(Swift_Events_CommandEvent $evt)
    {
        // TODO: Implement commandSent() method.
    }

}
