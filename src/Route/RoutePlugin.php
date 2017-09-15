<?php

namespace Spirit\Route;

use Spirit\Engine;
use Spirit\Route;
use Spirit\Structure\Plugin;

abstract class RoutePlugin extends Plugin {

    protected function loadRoute($route)
    {
        Engine::i()->includeFile(
            Engine::dir()->routes . $route
        );
    }

    protected function boot()
    {
        $this->routes(Route::routing());
    }

    abstract protected function routes(Routing $routing);

}