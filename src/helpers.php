<?php

use Spirit\View\Template;

if (!function_exists('dd')) {

    function dd($v)
    {
        if (!class_exists(\Spirit\Func\Trace::class)) {
            require_once "Func/Trace.php";
        }
        \Spirit\Func\Trace::it($v, false, false, true, debug_backtrace());
        die();
    }

}

if (!function_exists('t')) {

    function t($v)
    {
        if (!class_exists(\Spirit\Func\Trace::class, false)) {
            require_once "Func/Trace.php";
        }
        \Spirit\Func\Trace::it($v, false, false, true, debug_backtrace());
    }

}

if (!function_exists('trace')) {

    function trace($v)
    {
        \Spirit\Func\Trace::after($v);
    }

}

if (!function_exists('isAdminPanel')) {

    function isAdminPanel()
    {
        \Spirit\Services\Admin::init();
    }

}

if (!function_exists('e')) {

    /**
     * @param $value
     * @return string
     */
    function e($value)
    {
        return \Spirit\Func\Data::e($value);
    }

}

if (!function_exists('env')) {

    /**
     * @param $k
     * @param null $default
     * @return mixed|null
     */
    function env($k, $default = null)
    {
        if (!class_exists(\Spirit\Func\Trace::class, false)) {
            require_once "Config/Dotenv.php";
        }

        return \Spirit\Config\Dotenv::env($k, $default);
    }

}

if (!function_exists('isDebug')) {

    /**
     * @return boolean
     */
    function isDebug()
    {
        return env('APP_DEBUG', false);
    }

}

if (!function_exists('ext')) {

    /**
     * @param $v
     * @param null $file
     * @return \Spirit\View\Layout
     */
    function ext($v, $file = null)
    {
        return Template::extend($v, $file);
    }

}

if (!function_exists('block')) {

    function block($v)
    {
        return Template::block($v);
    }

}

if (!function_exists('view')) {

    /***
     * @param $path
     * @param array $data
     * @return \Spirit\View
     */
    function view($path, $data = [])
    {
        return \Spirit\View::make($path, $data);
    }

}

if (!function_exists('route')) {

    /**
     * @param $id
     * @param array $vars
     * @param bool $withHost
     * @return string
     */
    function route($id, $vars = [], $withHost = false)
    {
        return \Spirit\Route::makeUrlForAlias($id, $vars, $withHost);
    }

}

if (!function_exists('routeIs')) {

    /**
     * @param $alias
     * @return string
     */
    function routeIs($alias)
    {
        return \Spirit\Route::is($alias);
    }

}

if (!function_exists('css')) {
    function css($v, $ver = null)
    {
        return \Spirit\Response\FE::css($v, $ver);
    }
}

if (!function_exists('js')) {
    function js($v, $ver = null)
    {
        return \Spirit\Response\FE::js($v, $ver);
    }
}

if (!function_exists('inputToken')) {
    function inputToken()
    {
        return \Spirit\Response\FE::inputToken();
    }
}

if (!function_exists('old')) {
    function old($key)
    {
        return \Spirit\Request::old($key);
    }
}

if (!function_exists('lang')) {
    function lang($key, $data = null)
    {
        return \Spirit\Lang::get($key, $data);
    }
}

if (!function_exists('errors')) {
    function errors()
    {
        return \Spirit\Request::errors();
    }
}

if (!function_exists('session')) {
    function session($k, $default= null)
    {
        return \Spirit\Request\Session::get($k, $default);
    }
}