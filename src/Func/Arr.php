<?php

namespace Spirit\Func;

use Spirit\Collection;

class Arr
{

    public static function fromArgs($args)
    {
        if (count($args) == 1) {
            if (static::accessible($args[0])) {
                $keys = $args[0];
            } else {
                $keys = [$args[0]];
            }
        } else {
            $keys = $args;
        }

        return $keys;
    }

    public static function accessible($value)
    {
        return is_array($value) || $value instanceof \ArrayAccess;
    }

    /**
     * Преобразует массив в объект
     *
     * @param array $ar
     * @return object
     */
    public static function toObj($ar)
    {
        foreach($ar as &$i) {
            if (is_array($i) && array_values($i) !== $i) {
                $i = self::toObj($i);
            }
        }

        return (object)$ar;
    }

    static public function array_inside(&$array, $insertArray, $number, $after = true)
    {
        if (!is_numeric($number)) {
            $c = 0;
            foreach($array as $key => $item) {
                ++$c;
                if ($key == $number) {
                    $number = $c;
                    break;
                }
            }
        }

        if (!$after)
            $number = $number - 1;

        $elements_count = count($array);
        $arr1 = array_slice($array, 0, $number);
        $arr1 += $insertArray;
        $offset = $elements_count - $number;
        $arr2 = array_slice($array, -$offset, $offset);
        $array = $arr1 + $arr2;
    }

    static public function isArrayInArray($arr)
    {
        if (!is_array($arr))
            return false;

        return is_array(array_shift($arr));
    }

    public static function dot($array, $prepend = '')
    {
        $results = [];

        foreach($array as $key => $value) {
            if (is_array($value)) {
                $results = array_merge($results, static::dot($value, $prepend . $key . '.'));
            } else {
                $results[$prepend . $key] = $value;
            }
        }

        return $results;
    }

    public static function has($array, $key)
    {
        if (is_null($key)) {
            return false;
        }

        if (static::exists($array, $key)) {
            return true;
        }

        foreach(explode('.', $key) as $segment) {
            if (static::accessible($array) && static::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return false;
            }
        }

        return true;
    }

    public static function exists($array, $key)
    {
        if ($array instanceof \ArrayAccess) {
            return $array->offsetExists($key);
        }

        return array_key_exists($key, $array);
    }

    public static function isAssoc(array $array)
    {
        $keys = array_keys($array);

        return array_keys($keys) !== $keys;
    }

    public static function except($array, $keys)
    {
        static::forget($array, $keys);

        return $array;
    }

    public static function forget(&$array, $keys)
    {
        $original = &$array;

        $keys = (array)$keys;

        if (count($keys) === 0) {
            return;
        }

        foreach($keys as $key) {
            $parts = explode('.', $key);

            // clean up before each pass
            $array = &$original;

            while(count($parts) > 1) {
                $part = array_shift($parts);

                if (isset($array[$part]) && is_array($array[$part])) {
                    $array = &$array[$part];
                } else {
                    continue 2;
                }
            }

            unset($array[array_shift($parts)]);
        }
    }

    public static function pull(&$array, $key, $default = null)
    {
        $value = static::get($array, $key, $default);

        static::forget($array, $key);

        return $value;
    }

    public static function get($array, $key, $default = null)
    {
        if (is_null($key)) {
            return $array;
        }

        if (static::exists($array, $key)) {
            return $array[$key];
        }

        foreach(explode('.', $key) as $segment) {
            if (static::accessible($array) && static::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }

    public static function prepend($array, $value, $key = null)
    {
        if (is_null($key)) {
            array_unshift($array, $value);
        } else {
            $array = [$key => $value] + $array;
        }

        return $array;
    }

    public static function add($array, $key, $value)
    {
        if (is_null(static::get($array, $key))) {
            static::set($array, $key, $value);
        }

        return $array;
    }

    public static function set(&$array, $key, $value)
    {
        if (is_null($key)) {
            return $array = $value;
        }

        $keys = explode('.', $key);

        while(count($keys) > 1) {
            $key = array_shift($keys);

            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }

    public static function collapse($array)
    {
        $results = [];

        foreach($array as $values) {
            if ($values instanceof Collection) {
                $values = $values->all();
            } elseif (!is_array($values)) {
                continue;
            }

            $results = array_merge($results, $values);
        }

        return $results;
    }

    public static function pluck($array, $value, $key = null)
    {
        $results = [];

        list($value, $key) = static::explodePluckParameters($value, $key);

        foreach($array as $item) {
            $itemValue = Data::get($item, $value);

            if (is_null($key)) {
                $results[] = $itemValue;
            } else {
                $itemKey = Data::get($item, $key);

                $results[$itemKey] = $itemValue;
            }
        }

        return $results;
    }

    protected static function explodePluckParameters($value, $key)
    {
        $value = is_string($value) ? explode('.', $value) : $value;

        $key = is_null($key) || is_array($key) ? $key : explode('.', $key);

        return [$value, $key];
    }

    public static function only($array, $keys)
    {
        return array_intersect_key($array, array_flip((array)$keys));
    }

}