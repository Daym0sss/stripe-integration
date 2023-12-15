<?php

namespace Daymos\ExampleApp\Controllers;

use Daymos\ExampleApp\Core\Controller;
use Daymos\StripeRecurrent\Core\Stripe;

class UserController extends Controller
{
    public function new(): void
    {
        $this->app->render('register.html.twig');
    }

    public function store(): void
    {
        Stripe::createCustomer($_POST);
        header('Location: http://localhost:8000/users/get-login-page');
    }

    public function getLoginPage(): void
    {
        $this->app->render('login.html.twig');
    }

    public function login(): void
    {
        $result = Stripe::loginCustomer($_POST['email']);
        if ($result == null)
        {
            header('Location: http://localhost:8000/users/get-login-page');
        }
        else
        {
            session_start();
            $_SESSION['user_id'] = $result;
            session_write_close();
            header('Location: http://localhost:8000/');
        }
    }

    public function logout(): void
    {
        session_start();
        unset($_SESSION['user_id']);
        session_write_close();
        header('Location: http://localhost:8000/');
    }
}