<?php

namespace Tests\Route;

use Spirit\Structure\Middleware;

class MiddlewareCheck extends Middleware {

    public function handle($var = null)
    {
        return $var === 'number_one';
    }

}