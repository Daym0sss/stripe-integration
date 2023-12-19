<?php

namespace Daymos\ExampleApp\Controllers;

use Daymos\ExampleApp\Core\Controller;
use Daymos\StripeRecurrent\Core\Stripe;
use Daymos\StripeRecurrent\Core\Utils;
use Daymos\StripeRecurrent\Database\ErrorLog;
use Daymos\StripeRecurrent\Database\Payment;
use Daymos\StripeRecurrent\Database\Subscription;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\StripeClient;

class MainController extends Controller
{
    public function index(): void
    {
        session_start();
        $user_id = $_SESSION['user_id'];
        session_write_close();

        $subscriptions = [];
        if ($user_id != null)
        {
            $subscriptionsInfo = Stripe::getSubscriptions($user_id);
        }

        if (isset($subscriptionsInfo))
        {
            foreach ($subscriptionsInfo as $subscriptionInfo)
            {
                if ($subscriptionInfo['status'] == 'canceled')
                {
                    $unSubscriptionDate = date('F j, Y H:i:s', $subscriptionInfo['canceled_at']);
                }
                else
                {
                    $unSubscriptionDate = 'Not canceled yet';
                }

                $subscriptions []= [
                    'id' => $subscriptionInfo['id'],
                    'productName' => Stripe::getProduct($subscriptionInfo['items']['data'][0]['price']['product'])->name,
                    'nextPaymentDate' => date('F j, Y H:i:s', $subscriptionInfo['current_period_end']),
                    'expirationDate' => date('F j, Y H:i:s', $subscriptionInfo['current_period_end']),
                    'unSubscriptionDate' => $unSubscriptionDate,
                    'status' => $subscriptionInfo['status']
                ];
            }
        }

        $this->app->render('index.html.twig', [
            'user_id' => $user_id,
            'subscriptions' => $subscriptions
        ]);
    }

    public function createSubscription(): void
    {
        session_start();
        $user_id = $_SESSION['user_id'];
        session_write_close();

        $products = Stripe::getProducts();
        $customerData = Stripe::getCustomer($user_id);

        $this->app->render('subscription.html.twig', [
            'user_id' => $user_id,
            'products' => $products,
            'customer' => $customerData
        ]);
    }

    public function getPaymentStory(): void
    {
        session_start();
        $user_id = $_SESSION['user_id'];
        session_write_close();
        $history = Stripe::getPaymentStory($_POST['subscription_id']);

        $this->app->render('history.html.twig', [
            'user_id' => $user_id,
            'payments' => $history
        ]);
    }

    public function cancelSubscription(): void
    {
        Stripe::cancelSubscription($_POST['subscription_id']);
        header('Location: http://localhost:8000/');
    }

    public function storeSubscription(): void
    {
        session_start();
        $customer_id = $_SESSION['user_id'];
        session_write_close();
        $data = json_decode(file_get_contents('php://input'), true);
        try
        {
            $stripe = new StripeClient(Utils::env('STRIPE_SECRET'));
            $stripe->paymentIntents->create([
                'payment_method' => $data['paymentMethodId'],
                'amount' => $data['paymentAmount'],
                'currency' => 'usd',
                'confirmation_method' => 'manual',
                'confirm' => true,
                'off_session' => true,
            ]);

            $subscription = $stripe->subscriptions->create([
               'customer' => $customer_id,
                'items' => [
                    ['price' => $data['priceId']]
                ]
            ]);

            echo json_encode(['success' => true, 'subscription' => $subscription]);
        }
        catch (ApiErrorException $exception)
        {
            echo json_encode(['error' => $exception->getMessage()]);
        }
    }

    public function webhook(): void
    {
        \Stripe\Stripe::setApiKey(Utils::env('STRIPE_SECRET'));
        $payload = file_get_contents("php://input");
        $sig_header = $_SERVER["HTTP_STRIPE_SIGNATURE"];
        $event_data = json_decode($payload, true);

        switch ($event_data['type'])
        {
            case 'payment_intent.payment_failed':
            case 'payment_intent.succeeded':
            {
                $payment = $event_data['data']['object'];

                if (strlen($payment['customer']) > 5)
                {
                    Payment::create([
                        'payment_id' => $payment['id'],
                        'customer_id' => $payment['customer'],
                        'payment_datetime' => date('F j, Y H:i:s', $payment['created']),
                        'payment_status' => $payment['status']
                    ]);
                }

                break;
            }
            case 'customer.subscription.created':
            {
                $subscription = $event_data['data']['object'];
                Subscription::create([
                    'subscription_id' => $subscription['id'],
                    'customer_id' => $subscription['customer'],
                    'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
                    'updated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
                    'productName' => Stripe::getProduct($subscription['items']['data'][0]['price']['product'])->name,
                    'nextPaymentDate' => date('F j, Y H:i:s', $subscription['current_period_end']),
                    'expirationDate' => date('F j, Y H:i:s', $subscription['current_period_end']),
                    'unSubscriptionDate' => ($subscription['status'] != 'canceled')
                        ? 'Still active'
                        : date('F j, Y H:i:s', $subscription['canceled_at'])
                ]);

                break;
            }
            case 'customer.subscription.deleted':
            case 'customer.subscription.updated':
            {
                $subscription = $event_data['data']['object'];
                Subscription::update([
                    'subscription_id' => $subscription['id'],
                    'updated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
                    'nextPaymentDate' => date('F j, Y H:i:s', $subscription['current_period_end']),
                    'expirationDate' => date('F j, Y H:i:s', $subscription['current_period_end']),
                    'unSubscriptionDate' => ($subscription['status'] != 'canceled')
                        ? 'Still active'
                        : date('F j, Y H:i:s', $subscription['canceled_at'])
                ]);

                break;
            }
        }
    }
}