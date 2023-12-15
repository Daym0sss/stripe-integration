<?php

namespace Daymos\StripeRecurrent\Core;

use Stripe\Collection;
use Stripe\StripeClient;

class Stripe
{
    public static function createPaymentIntent()
    {

    }

    public static function getPaymentIntents(string $customer_id)
    {

    }

    public static function getSubscriptions(string $customer_id): array
    {
        $stripe = new StripeClient(Utils::env('STRIPE_SECRET'));
        return $stripe->subscriptions->all(['customer' => $customer_id])->data;
    }

    public static function createCustomer(array $params): void
    {
        $stripe = new StripeClient(Utils::env('STRIPE_SECRET'));
        $stripe->customers->create([
            'name' => $params['name'],
            'email' => $params['email'],
        ]);
    }
}