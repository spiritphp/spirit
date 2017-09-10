<?php

namespace Spirit;
// TODO
class Event
{

    const BEFORE_CONTROLLER = 'beforeController';
    const AFTER_CONTROLLER = 'afterController';

    protected static $events = [];

    public static function add($key, $callback, $label = false)
    {
        if (!isset(static::$events[$key])) {
            static::$events[$key] = [];
        }

        if (is_array($callback) && (!isset($callback[0]) || is_array($callback[0]))) {
            foreach ($callback as $key_callback => $value_callback) {
                if (!is_numeric($key_callback)) {
                    static::$events[$key][$key_callback] = $value_callback;
                } else {
                    static::$events[$key][] = $value_callback;
                }
            }
        } else {
            if ($label) {
                static::$events[$key][$label] = $callback;
            } else {
                static::$events[$key][] = $callback;
            }
        }

    }

    public static function init($key)
    {
        if (!isset(static::$events[$key])) return false;

        $result = [];
        foreach (static::$events[$key] as $label => $callback) {
            $result[$label] = $callback();
        }

        return $result;
    }

    public static function afterController($callback, $label = false)
    {
        static::add(static::AFTER_CONTROLLER, $callback, $label);
    }

    public static function beforeController($callback, $label = false)
    {
        static::add(static::BEFORE_CONTROLLER, $callback, $label);
    }
}