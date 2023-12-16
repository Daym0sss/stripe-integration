CREATE DATABASE stripe;
use stripe;
CREATE TABLE payments(payment_id INT NOT NULL AUTO_INCREMENT, payment_datetime VARCHAR(40), payment_status VARCHAR(50), PRIMARY KEY(payment_id));
CREATE TABLE subscriptions(subscription_id VARCHAR(30) NOT NULL, created_at DATETIME, productName VARCHAR(50), nextPaymentDate VARCHAR(40), expirationDate VARCHAR(40), unSubscriptionDate VARCHAR(40) DEFAULT NULL, PRIMARY KEY(subscription_id));
CREATE TABLE error_log(log_id INT NOT NULL AUTO_INCREMENT, log_datetime DATETIME, log_error_message text, PRIMARY KEY(log_id));