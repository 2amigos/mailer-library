<?php
namespace Da\Mailer\Test\Event;

use Da\Mailer\Event\Event;
use Da\Mailer\Event\EventHandlerTrait;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    public function testInvalidCallbackArgumentException()
    {
        $this->expectException(\Da\Mailer\Exception\InvalidCallbackArgumentException::class);

        new Event('not a callback');
    }

    public function testEventHandlerTraitMethods()
    {
        $triggered = null;
        $data = null;
        $handler = function (Event $event) use (&$triggered, &$data) {
            $triggered = $event;
            $data = $event->getData();
        };
        $event = new Event($handler);
        $eventsTester = new EventsTester();

        $eventsTester->attach('eventName', $event);

        $eventsTester->trigger('eventName', ['x-wings']);

        $this->assertEquals($triggered, $event);
        $this->assertEquals($data, ['x-wings']);

        $triggered = null;
        $data = null;
        $eventsTester->detach('eventName');
        $eventsTester->trigger('eventName');

        $this->assertNull($triggered);
        $this->assertNull($data);
    }
}

class EventsTester
{
    use EventHandlerTrait;
}
