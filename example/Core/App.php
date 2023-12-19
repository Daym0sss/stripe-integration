<?php

namespace Daymos\ExampleApp\Core;

use Daymos\ExampleApp\Controllers\MainController;
use Daymos\ExampleApp\Controllers\UserController;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class App
{
    private array $routes = [
        'GET' => [
            '/' => [
                'controller' => MainController::class,
                'action' => 'index'
            ],
            '/subscriptions/new' => [
                'controller' => MainController::class,
                'action' => 'createSubscription'
            ],
            '/users/new' => [
                'controller' => UserController::class,
                'action' => 'new'
            ],
            '/users/get-login-page' => [
                'controller' => UserController::class,
                'action' => 'getLoginPage'
            ],
            '/users/logout' => [
                'controller' => UserController::class,
                'action' => 'logout'
            ]
        ],
        'POST' => [
            '/subscriptions/cancel' => [
                'controller' => MainController::class,
                'action' => 'cancelSubscription'
            ],
            '/subscriptions/store' => [
                'controller' => MainController::class,
                'action' => 'storeSubscription'
            ],
            '/subscriptions/getPaymentStory' => [
                'controller' => MainController::class,
                'action' => 'getPaymentStory'
            ],
            '/users/create' => [
                'controller' => UserController::class,
                'action' => 'store'
            ],
            '/users/login' => [
                'controller' => UserController::class,
                'action' => 'login'
            ],
            '/webhooks' => [
                'controller' => MainController::class,
                'action' => 'webhook'
            ]
        ]
    ];

    public function run(): void
    {
        $path = $_SERVER['REQUEST_URI'];
        $method = $_SERVER['REQUEST_METHOD'];
        $routeData = $this->routes[$method][$path];
        if (!isset($routeData))
        {
            header('HTTP/1.0 404 Not Found', true, 404);
        }
        else
        {
            $controller = new $routeData['controller']($this);
            $method = $routeData['action'];
            $controller->$method();
        }
    }

    public function render(string $template, array $options = []): void
    {
        $loader = new FilesystemLoader($_SERVER['DOCUMENT_ROOT'] . '/example/Views');
        $twig = new Environment($loader);
        $template = $twig->load($template);

        echo $template->render($options);
    }
}