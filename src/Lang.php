<?php

namespace Spirit;

use Spirit\Lang\CodeProvider;

/**
 * Class Lang
 * @package Spirit
 *
 * @method static mixed get($k, $data = null)
 */
class Lang {

    protected static $storage = [];
    protected static $defaultCode;

    public static function code($code = null)
    {
        if (is_null($code)) {
            $code = static::getCode();
        }

        if (isset(static::$storage[$code])) {
            return static::$storage[$code];
        }

        return static::$storage[$code] = new CodeProvider($code);
    }

    public static function setCode($code)
    {
        static::$defaultCode = $code;
    }

    public static function getCode()
    {
        return static::$defaultCode ? static::$defaultCode : Engine::cfg()->lang;
    }

    public static function isCode($code)
    {
        return $code === static::getCode();
    }

    public static function __callStatic($name, $arguments)
    {
        return static::code()->$name(...$arguments);
    }

}