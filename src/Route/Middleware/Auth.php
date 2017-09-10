<?php

namespace Spirit\Route\Middleware;

use Spirit\Response\Redirect;
use Spirit\Structure\Middleware;
use Spirit\Auth as AuthSrc;

class Auth extends Middleware
{

    public function handle($var = null)
    {
        if (!AuthSrc::check()) {
            Redirect::to('login');
        }

        return true;
    }

}