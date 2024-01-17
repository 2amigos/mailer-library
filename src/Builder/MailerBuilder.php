<?php

namespace Da\Mailer\Builder;

use Da\Mailer\Mailer;
use Da\Mailer\Transport\TransportFactory;
use Exception;

class MailerBuilder extends Buildable
{
    /**
     * @param string|null $broker
     * @return Mailer
     * @throws Exception
     */
    public static function make($broker = null)
    {
        $config = self::getConfig();

        $transportType = $broker ?? $config['config']['transport'];
        $connectionValues = $config['transports'][$transportType];
        $transport = TransportFactory::create($connectionValues, $transportType)->create();

        return new Mailer($transport);
    }
}
