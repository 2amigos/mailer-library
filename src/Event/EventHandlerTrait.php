<?php
namespace Da\Mailer\Event;

/**
 *
 * EventHandlerTrait
 *
 * Provides Event Handling using mediator pattern.
 *
 * Date: 25/12/15
 * Time: 22:54
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 */
trait EventHandlerTrait
{
    /**
     * @var array $events
     */
    protected $events = [];

    public function attach($name, Event $event)
    {
        if (!isset($this->events[$name])) {
            $this->events[$name] = [];
        }

        $this->events[$name][] = $event;
    }

    public function detach($name)
    {
        if (array_key_exists($name, $this->events)) {
            unset($this->events[$name]);
        }
    }

    public function trigger($name, $data = null)
    {
        if (isset($this->events[$name])) {
            foreach ($this->events[$name] as $event) {
                $event->owner = $this;
                call_user_func_array($event, $data);
            }
        }
    }
}
