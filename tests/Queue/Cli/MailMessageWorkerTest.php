<?php
namespace Da\Mailer\Test\Queue\Cli;

use Da\Mailer\Event\Event;
use Da\Mailer\Mailer;
use Da\Mailer\Model\MailMessage;
use Da\Mailer\Queue\Cli\MailMessageWorker;
use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\SentMessage;

class MailMessageWorkerTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
    }

    public function testRunMethodOnSuccess()
    {
        $mailMessage = Mockery::mock(MailMessage::class);
        $sentMessage = Mockery::mock(SentMessage::class);

        /** @var Mailer $mailer */
        $mailer = Mockery::mock(Mailer::class)
            ->shouldReceive(['send' => $sentMessage])
            ->getMock();

        $worker = new MailMessageWorker($mailer, $mailMessage);

        $worker->attach('onSuccess', new Event(function($evt) {
            $this->assertInstanceOf(SentMessage::class, $evt->getData()[1]);
        }));

        $worker->run();
    }

    public function testRunMethodOnFailure()
    {
        $mailMessage = Mockery::mock(MailMessage::class);
        $sentMessage = null;

        /** @var Mailer $mailer */
        $mailer = Mockery::mock(Mailer::class)
            ->shouldReceive(['send' => $sentMessage])
            ->getMock();

        $worker = new MailMessageWorker($mailer, $mailMessage);

        $worker->attach('onFailure', new Event(function($evt) {
            $this->assertNull($evt->getData()[1]);
        }));

        $worker->run();
    }

    public function testRunMethodOnFailureDueToException()
    {
        $mailMessage = Mockery::mock(MailMessage::class);
        $sentMessage = Mockery::mock(SentMessage::class);

        /** @var Mailer $mailer */
        $mailer = Mockery::mock(Mailer::class)
            ->shouldReceive(['send' => $sentMessage])
            ->andThrow(new \Exception())
            ->getMock();

        $worker = new MailMessageWorker($mailer, $mailMessage);

        $worker->attach('onFailure', new Event(function($evt) {
            $this->assertNull($evt->getData()[1]);
        }));

        $worker->run();
    }
}
