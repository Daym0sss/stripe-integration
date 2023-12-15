<?php

namespace Daymos\StripeRecurrent\Core;
use Dotenv\Dotenv;

class Utils
{
    public static function env($key = null): array|string
    {
        $dotenv = Dotenv::createImmutable($_SERVER['DOCUMENT_ROOT']);
        $dotenv->load();

        if ($key != null)
        {
            return $_ENV[$key];
        }

        return $_ENV;
    }
}