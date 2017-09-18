<?php

namespace Spirit\Route\Middleware;

use Spirit\Response\Redirect;
use Spirit\Structure\Middleware;
use Spirit\Auth;

class Guest extends Middleware
{

    public function handle($var = null)
    {
        if (Auth::check()) {
            Redirect::home()->send();
        }

        return true;
    }

}