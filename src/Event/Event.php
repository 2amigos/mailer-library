<?php

namespace Da\Mailer\Event;

use Da\Mailer\Exception\InvalidCallbackArgumentException;

class Event
{
    /**
     * @var object the class firing the event.
     */
    public $owner;
/**
     * @var array the collected arguments passed to the event
     */
    private $data;
/**
     * @var callable
     */
    private $handler;
/**
     * Callback constructor.
     *
     * @param callable $handler
     */
    public function __construct($handler)
    {
        if (!is_callable($handler)) {
            throw new InvalidCallbackArgumentException('Argument is not callable!');
        }
        $this->handler = $handler;
    }

    /**
     * @return mixed
     */
    public function __invoke()
    {
        $this->data = func_get_args();
        return call_user_func($this->handler, $this);
    }

    /**
     * @return array the collected arguments passed to the event
     */
    public function getData()
    {
        return $this->data;
    }
}
