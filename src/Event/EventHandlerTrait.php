<?php
namespace Da\Mailer\Event;

trait EventHandlerTrait
{
    /**
     * @var array $events stack of events attached to the manager
     */
    protected $events = [];

    /**
     * Adds an Event instance to the stack based on the name.
     *
     * @param string $name the identifier of the stack
     * @param Event $event the event instance to add
     */
    public function attach($name, Event $event)
    {
        if (!isset($this->events[$name])) {
            $this->events[$name] = [];
        }

        $this->events[$name][] = $event;
    }

    /**
     * Removes the handlers of stack by its name.
     *
     * @param string $name
     */
    public function detach($name)
    {
        if (array_key_exists($name, $this->events)) {
            unset($this->events[$name]);
        }
    }

    /**
     * Fires the handlers of a stack by its name.
     *
     * @param string $name the name of the stack to fire
     * @param array $data
     */
    public function trigger($name, array $data = [])
    {
        if (isset($this->events[$name])) {
            foreach ($this->events[$name] as $event) {
                $event->owner = $this;
                call_user_func_array($event, $data);
            }
        }
    }
}
