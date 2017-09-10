<?php

namespace Spirit\Func;

use Spirit\Structure\Arrayable;
use Spirit\Structure\Jsonable;

class Str
{

    protected static $camelTo = [];
    protected static $camelFrom = [];

    static function translit($str)
    {
        return strtr(
            $str,
            [
                "А" => "A", "Б" => "B", "В" => "V", "Г" => "G",
                "Д" => "D", "Е" => "E", "Ж" => "J", "З" => "Z", "И" => "I",
                "Й" => "Y", "К" => "K", "Л" => "L", "М" => "M", "Н" => "N",
                "О" => "O", "П" => "P", "Р" => "R", "С" => "S", "Т" => "T",
                "У" => "U", "Ф" => "F", "Х" => "H", "Ц" => "TS", "Ч" => "CH",
                "Ш" => "SH", "Щ" => "SCH", "Ъ" => "", "Ы" => "YI", "Ь" => "",
                "Э" => "E", "Ю" => "YU", "Я" => "YA", "а" => "a", "б" => "b",
                "в" => "v", "г" => "g", "д" => "d", "е" => "e", "ж" => "j",
                "з" => "z", "и" => "i", "й" => "y", "к" => "k", "л" => "l",
                "м" => "m", "н" => "n", "о" => "o", "п" => "p", "р" => "r",
                "с" => "s", "т" => "t", "у" => "u", "ф" => "f", "х" => "h",
                "ц" => "ts", "ч" => "ch", "ш" => "sh", "щ" => "sch", "ъ" => "y",
                "ы" => "yi", "ь" => "", "э" => "e", "ю" => "yu", "я" => "ya"
            ]
        );
    }

    static public function toCamelCase($string)
    {
        if (isset(static::$camelTo[$string])) return static::$camelTo[$string];

        return static::$camelTo[$string] = lcfirst(
            str_replace(' ', '', ucwords(
                str_replace(['-', '_', '.'], ' ', $string)
            ))
        );
    }

    static public function toCamelCaseClass($string)
    {
        return ucfirst(static::toCamelCase($string));
    }

    static public function fromCamelCase($string)
    {
        if (isset(static::$camelFrom[$string])) return static::$camelFrom[$string];

        return static::$camelFrom[$string] = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $string));
    }

    static public function toString($v)
    {
        if (is_array($v)) {
            $v = json_encode($v, JSON_UNESCAPED_UNICODE);
        } elseif(is_object($v)) {
            if ($v instanceof Jsonable) {
                $v = $v->toJson(JSON_UNESCAPED_UNICODE);
            } else if ($v instanceof Arrayable) {
                $v = json_encode($v->toArray(),JSON_UNESCAPED_UNICODE);
            } else {
                $v = get_class($v);
            }
        }

        return $v;
    }

    static public function isJson($string)
    {
        if (!$string) return false;

        if ($string[0] === '{' && mb_substr($string, -1, 1, "UTF-8") === '}') return true;

        if ($string[0] === '[' && mb_substr($string, -1, 1, "UTF-8") === ']') return true;

        return false;
    }

    public static function lower($value)
    {
        return mb_strtolower($value, 'UTF-8');
    }

    public static function upper($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    public static function substr($string, $start, $length = null)
    {
        return mb_substr($string, $start, $length, 'UTF-8');
    }

    public static function ucfirst($string)
    {
        return static::upper(static::substr($string, 0, 1)) . static::substr($string, 1);
    }

    public static function random($length = 8)
    {
        $string = '';

        while (($len = strlen($string)) < $length) {
            $size = $length - $len;

            $bytes = random_bytes($size);

            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }

        return $string;
    }
}