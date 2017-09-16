<?php

namespace Spirit\Undercover;

use Spirit\Structure\Middleware;

class UndercoverMiddleware extends Middleware {

    /**
     * @param null $var
     * @return false|true
     */
    function handle($var = null)
    {
        Undercover::init();

        return true;
    }
}