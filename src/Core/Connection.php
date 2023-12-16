<?php

namespace Daymos\StripeRecurrent\Core;

class Connection
{
    private static \mysqli $connection;

    public static function getInstance()
    {
        if (!isset(self::$connection))
        {
            $config = Utils::env();
            self::$connection = new \mysqli($config['DB_HOST'], $config['DB_USER'], $config['DB_PASSWORD'], $config['DB_NAME']);
        }

        return self::$connection;
    }

    private function __construct()
    {
    }
}