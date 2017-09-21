<?php

namespace Spirit\Route\Middleware;

use Spirit\Request;
use Spirit\Request\Session;
use Spirit\Structure\Middleware;

class Token extends Middleware {


    /**
     * @param null $var
     * @return false|true
     */
    function handle($var = null)
    {
        if (Request::isGET()) {
            return true;
        }

        return hash_equals(Session::token(), Request::token());
    }
}