<?php

namespace Daymos\ExampleApp\Core;

abstract class Controller
{
    protected App $app;
    public function __construct(App $app)
    {
        $this->app = $app;
    }
}