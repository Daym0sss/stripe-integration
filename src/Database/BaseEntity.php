<?php

namespace Daymos\StripeRecurrent\Database;

abstract class BaseEntity
{
    protected string $table;
    protected string $primaryKey;
}