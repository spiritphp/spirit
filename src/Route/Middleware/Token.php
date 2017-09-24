<?php

namespace Spirit\Route\Middleware;

use Spirit\Error;
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

        if (!$req_token = Request::token()) {
            Error::abort(403, 'Token protect');
        }

        if (!hash_equals(Session::token(), $req_token)) {
            Error::abort(403, 'Token protect');
        }

        return true;
    }
}