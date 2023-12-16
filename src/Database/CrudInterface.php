<?php

namespace Daymos\StripeRecurrent\Database;

interface CrudInterface
{
    public static function create(array $params): void;
}