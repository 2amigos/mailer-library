<?php

namespace Da\Mailer\Queue\Backend;

use Da\Mailer\Model\MailMessage;

interface MailJobInterface
{
    /**
     * @return bool whether the mail job is a new instance or has been extracted from queue
     */
    public function isNewRecord();
/**
     * @param MailMessage|string $message
     */
    public function setMessage($message);
/**
     * @return MailMessage|string
     */
    public function getMessage();
/**
     * @return bool whether the job has been successfully completed
     */
    public function isCompleted();
}
