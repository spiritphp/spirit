<?php

namespace Spirit\Request;

use Spirit\Request;

/**
 * Class Cookie
 * @package Spirit\Response
 *
 * @method static mixed get($key = null, $default = null)
 * @method static mixed set($key, $value = null, $minutes = 52560, $path = '/', $domain = null, $secure = null, $httpOnly = true)
 * @method static mixed setSimple($key, $value = null, $minutes = 52560, $path = '/', $domain = null, $secure = null, $httpOnly = true)
 * @method static bool has(...$keys)
 * @method static array only(...$keys)
 * @method static array except(...$keys)
 * @method static void forget(...$keys)
 */
class Cookie
{

    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([Request::cookie(),$name],$arguments);
    }

}