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
        $values .= '"' . $params['payment_status'] . '", ';
        $values .= '"' . $params['customer_id'] . '"';
        $table = self::$tableName;

        $sql = "INSERT INTO $table(payment_id, payment_datetime, payment_status, customer_id) VALUES($values)";
        $conn = Connection::getInstance();
        $conn->query($sql);
    }

    public static function update(array $params): void
    {
        // TODO: Implement update() method.
    }
}