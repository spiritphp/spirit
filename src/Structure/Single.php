<?php

namespace Spirit\Structure;

/**
 * Class Single
 * @package Spirit\Structure
 */
abstract class Single
{

    /**
     * @var static[]
     */
    protected static $instance = [];

    protected static function getClassName()
    {
        return get_called_class();
    }

    /**
     * @return static
     */
    public static function getInstance()
    {
        /**
         * static
         */
        $className = static::getClassName();

        if (!isset(static::$instance[$className])) {
            static::$instance[$className] = new $className;
        }

        return static::$instance[$className];
    }

}