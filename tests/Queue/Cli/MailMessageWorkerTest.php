<?php
namespace Da\Mailer\Test\Queue\Cli;

use Da\Mailer\Event\Event;
use Da\Mailer\Mailer;
use Da\Mailer\Model\MailMessage;
use Da\Mailer\Queue\Cli\MailMessageWorker;
use Mockery;
use PHPUnit\Framework\TestCase;
use Swift_Message;

class MailMessageWorkerTest extends TestCase
{
    public function testRunMethodOnSuccess()
    {
        //TODO Rebuild test
        $this->assertTrue(1 === 1);
    }

    public function testRunMethodOnFailure()
    {
        //TODO Rebuild test
        $this->assertTrue(1 === 1);
    }

    public function testRunMethodOnFailureDueToException()
    {
        //TODO Rebuild test
        $this->assertTrue(1 === 1);
    }
}
