<?php

namespace Da\Mailer\Model;

use Da\Mailer\Queue\Backend\MailJobInterface;

class MailJob extends AbstractMailObject implements MailJobInterface
{
    /**
     * @var mixed
     */
    private $id;
/**
     * @var MailMessage|string
     */
    private $message;
/**
     * @var int number of attempts. Every time a mail fails to be sent, the number of attempts could be incremented.
     *
     * @see `incrementAttempt()`
     */
    private $attempt = 0;
/**
     * @var bool whether the job has been completed
     */
    private $completed = false;
/**
     * {@inheritdoc}
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    /**
     * @return bool
     */
    public function isNewRecord()
    {
        return $this->getId() === null;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return MailMessage|string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param MailMessage|string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return int
     */
    public function getAttempt()
    {
        return $this->attempt;
    }

    /**
     * @param $attempt
     */
    public function setAttempt($attempt)
    {
        $this->attempt = $attempt;
    }

    /**
     * Increments attempt by one.
     */
    public function incrementAttempt()
    {
        $this->attempt += 1;
    }

    /**
     * @return bool
     */
    public function markAsCompleted()
    {
        return $this->completed = true;
    }

    /**
     * @return bool
     */
    public function isCompleted()
    {
        return $this->completed === true;
    }
}
