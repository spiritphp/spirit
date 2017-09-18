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
        $routing = Route::routing();

        $this->middleware($routing);
        $this->routes($routing);
    }

    abstract protected function routes(Routing $routing);

    protected function middleware(Routing $routing) {

    }

}