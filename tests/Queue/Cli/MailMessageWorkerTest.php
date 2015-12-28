<?php
namespace Da\Tests\Queue\Cli;

use Da\Mailer\Mailer;
use Da\Mailer\Model\MailMessage;
use Da\Mailer\Queue\Cli\MailMessageWorker;
use Da\Mailer\Event\Event;
use Mockery;
use PHPUnit_Framework_TestCase;
use Swift_Message;

class MailMessageWorkerTest extends PHPUnit_Framework_TestCase
{

    public function testRunMethodOnSuccess()
    {
        $swift = new Swift_Message();
        $mockedMailMessage = Mockery::mock(MailMessage::class);

        $mockedMailMessage
            ->shouldReceive('asSwiftMessage')
            ->once()
            ->andReturn($swift);

        $mockedMailer = Mockery::mock( Mailer::class);

        $mockedMailer
            ->shouldReceive('sendSwiftMessage')
            ->once()
            ->with($swift)
            ->andReturn(null);

        $mailMessageWorker = new MailMessageWorker($mockedMailer, $mockedMailMessage);
        $eventResponse = null;
        $failedRecipientsResponse = 0;

        $handler = function(Event $event) use (&$eventResponse, &$failedRecipientsResponse){
            $data = $event->getData();
            $eventResponse = $data[0];
            $failedRecipientsResponse = $data[1];
        };
        $onSuccessEvent = new Event($handler);

        $mailMessageWorker->attach('onSuccess', $onSuccessEvent);

        $mailMessageWorker->run();

        $this->assertEquals($eventResponse, $mockedMailMessage);
        $this->assertEquals($failedRecipientsResponse, null);
    }

    public function testRunMethodOnFailure()
    {
        $swift = new Swift_Message();
        $mockedMailMessage = Mockery::mock(MailMessage::class);

        $mockedMailMessage
            ->shouldReceive('asSwiftMessage')
            ->once()
            ->andReturn($swift);

        $mockedMailMessage->to = 'failed@mail.com';

        $mockedMailer = Mockery::mock( Mailer::class);

        $mockedMailer
            ->shouldReceive('sendSwiftMessage')
            ->once()
            ->with($swift)
            ->andReturn(['failed@mail.com']);

        $mailMessageWorker = new MailMessageWorker($mockedMailer, $mockedMailMessage);
        $eventResponse = null;
        $failedRecipientsResponse = 0;

        $handler = function(Event $event) use (&$eventResponse, &$failedRecipientsResponse){
            $data = $event->getData();
            $eventResponse = $data[0];
            $failedRecipientsResponse = $data[1];
        };
        $onSuccessEvent = new Event($handler);

        $mailMessageWorker->attach('onFailure', $onSuccessEvent);

        $mailMessageWorker->run();

        $this->assertEquals($eventResponse, $mockedMailMessage);
        $this->assertEquals($failedRecipientsResponse, ['failed@mail.com']);
    }

    public function testRunMethodOnFailureDueToException()
    {
        $swift = new Swift_Message();
        $mockedMailMessage = Mockery::mock(MailMessage::class);

        $mockedMailMessage
            ->shouldReceive('asSwiftMessage')
            ->once()
            ->andReturn($swift);

        $mockedMailMessage->to = 'failed@mail.com';

        $mockedMailer = Mockery::mock( Mailer::class);

        $mockedMailer
            ->shouldReceive('sendSwiftMessage')
            ->once()
            ->with($swift)
            ->andThrow('Exception');

        $mailMessageWorker = new MailMessageWorker($mockedMailer, $mockedMailMessage);
        $eventResponse = null;
        $failedRecipientsResponse = 0;

        $handler = function(Event $event) use (&$eventResponse, &$failedRecipientsResponse){
            $data = $event->getData();
            $eventResponse = $data[0];
            $failedRecipientsResponse = $data[1];
        };
        $onSuccessEvent = new Event($handler);

        $mailMessageWorker->attach('onFailure', $onSuccessEvent);

        $mailMessageWorker->run();

        $this->assertEquals($eventResponse, $mockedMailMessage);
        $this->assertEquals($failedRecipientsResponse, ['failed@mail.com']);
    }
}
