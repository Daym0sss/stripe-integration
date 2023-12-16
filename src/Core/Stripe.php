<?php

namespace Daymos\StripeRecurrent\Core;

use Stripe\Collection;
use Stripe\Price;
use Stripe\Product;
use Stripe\StripeClient;

class Stripe
{
    public static function getProducts(): array
    {
        $stripe = new StripeClient(Utils::env('STRIPE_SECRET'));
        $prices = $stripe->prices->all()->data;
        $data = [];
        foreach ($prices as $price)
        {
            $data []= [
                'price_id' => $price->id,
                'product' => [
                    'name' => $stripe->products->retrieve($price->product)->name,
                    'price_value' => number_format($price->unit_amount / 100, 2) . ' ' . strtoupper($price->currency)
                ]
            ];
        }

        return $data;
    }

    public static function getProduct(string $product_id): Product
    {
        $stripe = new StripeClient(Utils::env('STRIPE_SECRET'));
        return $stripe->products->retrieve($product_id);
    }

    public static function getPrice(string $price_id): Price
    {
        $stripe = new StripeClient(Utils::env('STRIPE_SECRET'));
        return $stripe->prices->retrieve($price_id);
    }


    public static function getSubscriptions(string $customer_id): array
    {
        $stripe = new StripeClient(Utils::env('STRIPE_SECRET'));
        return $stripe->subscriptions->all(['customer' => $customer_id, 'status' => 'all'])->data;
    }

    public static function getPaymentIntents(string $subscription_id): array
    {
        $stripe = new StripeClient(Utils::env('STRIPE_SECRET'));
        $invoices = $stripe->invoices->all(['subscription' => $subscription_id]);
        $payments = [];
        foreach ($invoices as $invoice)
        {
            $payments []= $stripe->paymentIntents->retrieve($invoice->payment_intent);
        }

        return $payments;
    }

    public static function getPaymentStory(string $subscription_id): array
    {
        $stripe = new StripeClient(Utils::env('STRIPE_SECRET'));
        $invoices = $stripe->invoices->all(['subscription' => $subscription_id]);
        $payments = [];
        foreach ($invoices->data as $invoice)
        {
            $payments []= [
                'sum' => number_format($invoice->amount_paid / 100, 2),
                'currency' => strtoupper($invoice->currency),
                'status' => $invoice->status,
                'date' => date('F j, Y H:i:s', $invoice->created)
            ];
        }

        return $payments;
    }


    public static function cancelSubscription(string $subscription_id): void
    {
        $stripe = new StripeClient(Utils::env('STRIPE_SECRET'));
        $stripe->subscriptions->retrieve($subscription_id)->cancel();
    }

    public static function createCustomer(array $params): void
    {
        $stripe = new StripeClient(Utils::env('STRIPE_SECRET'));
        $stripe->customers->create([
            'name' => $params['name'],
            'email' => $params['email'],
        ]);
    }

    public static function loginCustomer(string $email): ?string
    {
        $stripe = new StripeClient(Utils::env('STRIPE_SECRET'));
        $customers = $stripe->customers->all(['email' => $email, 'limit' => 1])->data;
        if (count($customers) > 0)
        {
            return $customers[0]->id;
        }

        return null;
    }

    public static function getCustomer(string $customer_id): array
    {
        $stripe = new StripeClient(Utils::env('STRIPE_SECRET'));
        $customer = $stripe->customers->retrieve($customer_id);

        return [
            'email' => $customer->email,
            'name' => $customer->name
        ];
    }
}