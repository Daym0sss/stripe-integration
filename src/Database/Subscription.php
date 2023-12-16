<?php

namespace Daymos\StripeRecurrent\Database;

use Daymos\StripeRecurrent\Core\Connection;

class Subscription extends BaseEntity implements CrudInterface
{
    protected static string $tableName = "subscriptions";

    public static function create(array $params): void
    {
        $values = '"' . $params['subscription_id'] . '", ';
        $values .= '"' . $params['created_at'] . '", ';
        $values .= '"' . $params['productName'] . '", ';
        $values .= '"' . $params['nextPaymentDate'] . '", ';
        $values .= '"' . $params['expirationDate'] . '", ';
        $values .= '"' . $params['unSubscriptionDate'] . '"';
        $table = self::$tableName;

        $sql = "INSERT INTO $table(subscription_id, created_at, productName, nextPaymentDate, expirationDate, unSubscriptionDate) VALUES($values)";
        $conn = Connection::getInstance();
        $conn->query($sql);
    }
}