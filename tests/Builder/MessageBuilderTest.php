<?php

namespace Da\Mailer\Test\Builder;

use Da\Mailer\Builder\MessageBuilder;
use Da\Mailer\Mail\Dto\EmailAddress;
use Da\Mailer\Model\MailMessage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Address;

class MessageBuilderTest extends TestCase
{
    public function testMake()
    {
        $message = MessageBuilder::make(new MailMessage([
            'from' => EmailAddress::make('test@from.com', 'Sender'),
            'to' => EmailAddress::make('test@to.com','Receiver'),
            'cc' => [
                EmailAddress::make('test@cc.com','Copy'),
                EmailAddress::make('test@cc2.com','Copy2'),
            ],
            'bcc' => 'test@bcc.com',
            'subject' => 'ola',
        ]));

        /** @var Address $from */
        $from = $message->getFrom()[0];
        $this->assertEquals($from->getAddress(), 'test@from.com');
        $this->assertEquals($from->getName(), 'Sender');

        /** @var Address $to */
        $to = $message->getTo()[0];
        $this->assertEquals($to->getAddress(), 'test@to.com');
        $this->assertEquals($to->getName(), 'Receiver');

        /** @var Address $cc */
        $cc = $message->getCc()[0];
        $this->assertEquals($cc->getAddress(), 'test@cc.com');
        $this->assertEquals($cc->getName(), 'Copy');

        /** @var string $cc */
        $bcc = $message->getBcc()[0];
        $this->assertEquals($bcc->getAddress(), 'test@bcc.com');
    }

    public function testBodyText()
    {
        $message = MessageBuilder::make(new MailMessage([
            'from' => EmailAddress::make('test@from.com', 'Sender'),
            'to' => EmailAddress::make('test@to.com','Receiver'),
            'cc' => [
                EmailAddress::make('test@cc.com','Copy'),
                EmailAddress::make('test@cc2.com','Copy2'),
            ],
            'bcc' => 'test@bcc.com',
            'subject' => 'ola',
            'bodyText' => 'text body!'
        ]));

        $this->assertEquals($message->getTextBody(), 'text body!');
    }

    public function testResourceBody()
    {
        $mailMessage = new MailMessage([
            'from' => EmailAddress::make('test@from.com', 'Sender'),
            'to' => EmailAddress::make('test@to.com','Receiver'),
            'cc' => [
                EmailAddress::make('test@cc.com','Copy'),
                EmailAddress::make('test@cc2.com','Copy2'),
            ],
            'bcc' => 'test@bcc.com',
            'subject' => 'ola',
            'bodyText' => __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'text-body.txt'
        ]);

        $message = MessageBuilder::make($mailMessage);
        $this->assertTrue(is_resource($message->getTextBody()));
        $this->assertEquals("file text body!\n", stream_get_contents($message->getTextBody()));

        $mailMessage->bodyHtml = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'text-body.html';
        $message2 = MessageBuilder::make($mailMessage);
        $this->assertTrue(is_resource($message2->getHtmlBody()));
        $this->assertEquals("file <b>html</b> body!\n", stream_get_contents($message2->getHtmlBody()));
    }

    public function testAttachments()
    {
        $mailMessage = new MailMessage([
            'from' => EmailAddress::make('test@from.com', 'Sender'),
            'to' => EmailAddress::make('test@to.com','Receiver'),
            'cc' => [
                EmailAddress::make('test@cc.com','Copy'),
                EmailAddress::make('test@cc2.com','Copy2'),
            ],
            'bcc' => 'test@bcc.com',
            'subject' => 'ola'
        ]);
        $mailMessage->addAttachment(
            __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'text-body.txt',
                'text-file,text'
            );
        $message = MessageBuilder::make($mailMessage);

        $this->assertEquals("file text body!\n", $message->getAttachments()[0]->getBody());
    }
}
