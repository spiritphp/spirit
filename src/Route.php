<?php

namespace Spirit;

use Spirit\Route\Current;
use Spirit\Route\Routing;

/**
 * Class Route
 * @package Spirit
 *
 * @method static Current current()
 * @method static void add($path, $options)
 * @method static void post($path, $options)
 * @method static void get($path, $options)
 * @method static void match($methods, $path, $options)
 * @method static void group($options, $callback)
 * @method static array getRoutes()
 * @method static string makeUrlForAlias($id, $vars = [])
 * @method static Current parse($path)
 * @method static void addMiddleware($key, $middlewareClassName)
 */
class Route
{
    /**
     * @var Routing
     */
    protected static $routing;

    public static function make($initialRoutes = null)
    {
        return new Routing($initialRoutes);
    }

    public static function init($path = null)
    {
        $initialRoutes = null;
        if (Engine::cfg()->enableCommonRoute) {
            $initialRoutes = Engine::i()
                ->includeFile(Engine::i()->spirit_path . 'Common/route.php');
        }

        static::$routing = static::make($initialRoutes);

        if ($path) {
            Engine::i()->includeFile($path);
        }

        return static::$routing;
    }

    /**
     * @return  Routing
     */
    public static function routing()
    {
        return static::$routing;
    }

    public static function __callStatic($name, $arguments)
    {
        if (!static::$routing) return null;

        return static::$routing->$name(...$arguments);
    }

}