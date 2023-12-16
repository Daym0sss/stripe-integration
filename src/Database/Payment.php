<?php

namespace Daymos\StripeRecurrent\Database;

use Daymos\StripeRecurrent\Core\Connection;

class Payment extends BaseEntity implements CrudInterface
{
    protected static string $tableName = "payments";

    public static function create(array $params): void
    {
        $values = '"' . $params['payment_id'] . '", ';
        $values .= '"' . $params['payment_datetime'] . '", ';
        $values .= '"' . $params['payment_status'] . '"';
        $table = self::$tableName;

        $sql = "INSERT INTO $table(payment_id, payment_datetime, payment_status) VALUES($values)";
        $conn = Connection::getInstance();
        $conn->query($sql);
    }
}