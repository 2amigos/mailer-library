<?php

namespace Da\Mailer\Builder;

use Da\Mailer\Helper\ConfigReader;
use Exception;

abstract class Buildable
{
    /**
     * @return array
     * @throws Exception
     */
    protected static function getConfig()
    {
        return ConfigReader::get();
    }

    /**
     * @param $config
     * @return void
     */
    abstract public static function make($config = null);
}
