<?php

namespace Spirit\Func;

use Spirit\Collection;

class Data
{

    static function e($value)
    {
        return htmlentities($value, ENT_QUOTES, 'UTF-8', false);
    }

    /**
     * Return the default value of the given value.
     *
     * @param  mixed $value
     * @return mixed
     */
    public static function value($value)
    {
        return $value instanceof \Closure ? $value() : $value;
    }

    /**
     * Get an item from an array or object using "dot" notation.
     *
     * @param  mixed $target
     * @param  string|array $key
     * @param  mixed $default
     * @return mixed
     */
    public static function get($target, $key, $default = null)
    {
        if (is_null($key)) {
            return $target;
        }

        $key = is_array($key) ? $key : explode('.', $key);

        while (($segment = array_shift($key)) !== null) {
            if ($segment === '*') {
                if ($target instanceof Collection) {
                    $target = $target->all();
                } elseif (!is_array($target)) {
                    return static::value($default);
                }

                $result = Arr::pluck($target, $key);

                return in_array('*', $key) ? Arr::collapse($result) : $result;
            }

            if (Arr::accessible($target) && Arr::exists($target, $segment)) {
                $target = $target[$segment];
            } elseif (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
            } else {
                return static::value($default);
            }
        }

        return $target;
    }

    /**
     * Set an item on an array or object using dot notation.
     *
     * @param  mixed $target
     * @param  string|array $key
     * @param  mixed $value
     * @param  bool $overwrite
     * @return mixed
     */
    public static function set(&$target, $key, $value, $overwrite = true)
    {
        $segments = is_array($key) ? $key : explode('.', $key);

        if (($segment = array_shift($segments)) === '*') {
            if (!Arr::accessible($target)) {
                $target = [];
            }

            if ($segments) {
                foreach ($target as &$inner) {
                    static::set($inner, $segments, $value, $overwrite);
                }
            } elseif ($overwrite) {
                foreach ($target as &$inner) {
                    $inner = $value;
                }
            }
        } elseif (Arr::accessible($target)) {
            if ($segments) {
                if (!Arr::exists($target, $segment)) {
                    $target[$segment] = [];
                }

                static::set($target[$segment], $segments, $value, $overwrite);
            } elseif ($overwrite || !Arr::exists($target, $segment)) {
                $target[$segment] = $value;
            }
        } elseif (is_object($target)) {
            if ($segments) {
                if (!isset($target->{$segment})) {
                    $target->{$segment} = [];
                }

                static::set($target->{$segment}, $segments, $value, $overwrite);
            } elseif ($overwrite || !isset($target->{$segment})) {
                $target->{$segment} = $value;
            }
        }

        return $target;
    }

    public static function clearStrong($val)
    {
        if (is_array($val)) return null;

        return preg_replace("/[^@\/_\-\.,=%A-Za-z0-9:+А-Яа-яЁё\s]/iu", '', trim($val));
    }

    public static function clear($value, $htmlAllowed = false, $maxSubNum = 1, $curSubNum = 0)
    {
        if ($curSubNum >= $maxSubNum) {
            return null;
        }

        if (!is_string($value)) {
            foreach ($value as $k => $v) {
                $k = static::clearStrong($k);
                $value[$k] = static::clear($v, $htmlAllowed, $maxSubNum, ($curSubNum + 1));
            }
        } else {
            $value = trim($value);

            if (!$htmlAllowed) {
                $value = strip_tags($value);
                $value = static::e($value);
                $value = strtr($value, [
                    "\r" => "",
                    "\t" => ""
                ]);
            }
        }

        return $value;
    }
}