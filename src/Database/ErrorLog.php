<?php

namespace Daymos\StripeRecurrent\Database;

use Daymos\StripeRecurrent\Core\Connection;

class ErrorLog extends BaseEntity implements CrudInterface
{
    protected static string $tableName = "error_log";

    public static function create(array $params): void
    {
        $values = '"' . $params['log_datetime'] . '", ';
        $values .= '"' . $params['log_error_message'] . '"';
        $table = self::$tableName;

        $sql = "INSERT INTO $table(log_datetime, log_error_message) VALUES($values)";
        $conn = Connection::getInstance();
        $conn->query($sql);
    }
}