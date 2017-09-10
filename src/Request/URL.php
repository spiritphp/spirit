<?php

namespace Spirit\Request;

use Spirit\Engine;
use Spirit\Request;
use Spirit\Route;

class URL
{

    static public function href()
    {
        $get = Request::query()->all();
        $q_params = '?' . http_build_query($get);

        return static::current() . Request::fullPath() . $q_params;
    }

    static public function path()
    {
        return static::current() . Request::fullPath();
    }

    static public function current()
    {
        return Engine::i()->url;
    }

    static public function domain()
    {
        return Engine::i()->domain;
    }

    static public function referer()
    {
        if (
            isset($_SERVER['HTTP_REFERER']) &&
            $_SERVER['HTTP_REFERER'] != '' &&
            is_string($_SERVER['HTTP_REFERER'])
        ) {
            return $_SERVER['HTTP_REFERER'];
        }

        return null;
    }

    static public function route($route_name, $vars = [])
    {
        if (!is_array($vars)) $vars = [];

        return Route::makeUrlForAlias($route_name, $vars);
    }

    static public function back()
    {
        if ($back = static::referer()) {
            return $back;
        }

        return static::current();
    }

    static public function make($path, $queryParams = null)
    {
        if (mb_substr($path, 0, 1, "UTF-8") === '/') {
            $path = mb_substr($path, 1, null, "UTF-8");
        }

        if ($queryParams && is_array($queryParams) && count($queryParams)) {
            $queryParams = (strpos($path,'?') === 0 ? '?' : '&' ) . http_build_query($queryParams);
        } else {
            $queryParams = '';
        }

        return static::current() . $path . $queryParams;
    }

}