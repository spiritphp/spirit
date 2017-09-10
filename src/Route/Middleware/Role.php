<?php

namespace Spirit\Route\Middleware;

use App\U;
use Spirit\Auth;
use Spirit\Error;
use Spirit\Structure\Middleware;

class Role extends Middleware
{

    public function handle($role = false)
    {
        if ($role === 'debug' && isDebug())  {
            return true;
        }

        if (!$role || !Auth::check()) {
            Error::abort(403);
        }

        if (!U::acl($role)) {
            Error::abort(403);
        }

        return true;
    }

}