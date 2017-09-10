<?php

namespace Spirit\Func;

use Spirit\Engine;

class Hash
{

    const TYPE_256 = 256;

    public static function h($arr = [], $type = null)
    {
        if (!is_array($arr)) {
            $arr = [$arr];
        }

        $str = implode('::', $arr) . Engine::cfg()->appKey;

        if ($type === static::TYPE_256) {
            return hash('sha256', $str);
        }

        return sha1($str);
    }

    public static function h256($arr)
    {
        return static::h($arr, static::TYPE_256);
    }
}