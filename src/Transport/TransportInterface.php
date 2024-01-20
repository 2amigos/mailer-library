<?php

namespace Da\Mailer\Transport;

interface TransportInterface
{
    /**
     * @return \Symfony\Component\Mailer\Transport\TransportInterface
     */
    public function getInstance(): \Symfony\Component\Mailer\Transport\TransportInterface;
}
