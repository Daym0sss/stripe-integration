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
        $values .= '"' . $params['unSubscriptionDate'] . '", ';
        $values .= '"' . $params['updated_at'] . '", ';
        $values .= '"' . $params['customer_id'] . '"';
        $table = self::$tableName;

        $sql = "INSERT INTO $table(subscription_id, created_at, productName, nextPaymentDate, expirationDate, unSubscriptionDate, updated_at, customer_id) VALUES($values)";
        $conn = Connection::getInstance();
        $conn->query($sql);
    }

    public static function update(array $params): void
    {
        $table = self::$tableName;
        $setters = 'nextPaymentDate = "' . $params['nextPaymentDate'] . '", ';
        $setters .= 'expirationDate = "' . $params['expirationDate'] . '", ';
        $setters .= 'unSubscriptionDate = "' . $params['unSubscriptionDate'] . '", ';
        $setters .= 'updated_at = "' . $params['updated_at'] . '"';
        $sql = "UPDATE $table SET $setters WHERE subscription_id = \"{$params['subscription_id']}\"";
        $conn = Connection::getInstance();
        $conn->query($sql);
    }
}