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

            $paymentIntents = Stripe::getPaymentIntents($subscription->id);

            foreach ($paymentIntents as $paymentIntent)
            {
                /**
                 * @var $paymentIntent PaymentIntent
                 */
                Payment::create([
                    'payment_id' => $paymentIntent->id,
                    'payment_datetime' => date('F j, Y H:i:s', $paymentIntent->created),
                    'payment_status' => $paymentIntent->status
                ]);
            }

            Subscription::create([
                'subscription_id' => $subscription->id,
                'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
                'productName' => Stripe::getProduct($subscription->items->data[0]->price->product)->name,
                'nextPaymentDate' => date('F j, Y H:i:s', $subscription->current_period_end),
                'expirationDate' => date('F j, Y H:i:s', $subscription->current_period_end),
                'unSubscriptionDate' => ($subscription->status == 'canceled')
                                        ? 'Still active'
                                        : date('F j, Y H:i:s', $subscription->canceled_at)
            ]);


            echo json_encode(['success' => true, 'subscription' => $subscription]);
        }
        catch (ApiErrorException $exception)
        {
            ErrorLog::create([
                'log_datetime' => (new \DateTime())->format('Y-m-d H:i:s'),
                'log_error_message' => $exception->getMessage()
            ]);

            echo json_encode(['error' => $exception->getMessage()]);
        }
    }
}