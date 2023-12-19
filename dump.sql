CREATE DATABASE stripe;
use stripe;
CREATE TABLE payments(payment_id VARCHAR(30) NOT NULL, customer_id VARCHAR(30), payment_datetime VARCHAR(40), payment_status VARCHAR(50), PRIMARY KEY(payment_id));
CREATE TABLE subscriptions(subscription_id VARCHAR(30) NOT NULL, created_at DATETIME, productName VARCHAR(50), customer_id VARCHAR(30), updated_at DATETIME, nextPaymentDate VARCHAR(40), expirationDate VARCHAR(40), unSubscriptionDate VARCHAR(40) DEFAULT NULL, PRIMARY KEY(subscription_id));