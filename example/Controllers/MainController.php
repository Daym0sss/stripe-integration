<?php

namespace Daymos\ExampleApp\Controllers;

use Daymos\ExampleApp\Core\Controller;
use Daymos\StripeRecurrent\Core\Stripe;

class MainController extends Controller
{
    public function index(): void
    {
        /*session_start();
        $user_id = $_SESSION['user_id'];
        session_write_close();*/
        $user_id = 'cus_PBmTDJW6Injzpr';
        $subscriptions = [];
        if ($user_id != null)
        {
            $subscriptionsInfo = Stripe::getSubscriptions($user_id);
        }

        foreach ($subscriptionsInfo as $subscriptionInfo)
        {
            $subscriptions []= [

            ];
        }

        $this->app->render('index.html.twig', [
            'user_id' => $user_id,
            'subscriptions' => $subscriptions
        ]);
    }

    public function createPayment(): void
    {
        $this->app->render('payment.html.twig');
    }

    public function savePayment(): void
    {
        Stripe::paymentIntent();
    }
}