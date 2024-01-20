<?php

namespace Da\Mailer\Helper;

use Exception;

class ConfigReader
{
    /**
     * @return array
     * @throws Exception
     */
    public static function get(): array
    {
        $configPath = require self::getBasePath() . 'config/mailer.php';

        if (! is_array($configPath)) {
            throw new Exception('The configuration format is invalid. It must be an array!');
        }

        return $configPath;
    }

    /**
     * @return string
     */
    public static function getBasePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
    }
}
