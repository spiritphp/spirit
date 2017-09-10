<?php

namespace Spirit\Func;

class Num
{

    public static $theEnd = [
        'D' => ['день', 'дня', 'дней', ''],
        'H' => ['час', 'часа', 'часов', ''],
        'min' => ['минута', 'минуты', 'минут', ''],
        'min_1' => ['минуту', 'минуты', 'минут', ''],
        'sec' => ['секунда', 'секунды', 'секунд', ''],
        'sec_1' => ['секунду', 'секунды', 'секунд', ''],
        'rub' => ['рубль', 'рубля', 'рублей'],
        'kop' => ['копейка', 'копейки', 'копеек'],
        'sht' => ['штука', 'штуки', 'штук'],
        'mess' => ['сообщение', 'сообщения', 'сообщений', 'Сообщений нет'],
        'people' => ['человек', 'человека', 'человек', 'Никого нет']
    ];

    public static $romanicNumber = [
        'M' => 1000,
        'CM' => 900,
        'D' => 500,
        'CD' => 400,
        'C' => 100,
        'XC' => 90,
        'L' => 50,
        'XL' => 40,
        'X' => 10,
        'IX' => 9,
        'V' => 5,
        'IV' => 4,
        'I' => 1
    ];

    /**
     * @param $count
     * @param array $theend array('заканчивающиеся на 1,кроме 11','на 2-4(кроме 12-14)','на 5-9,0,11-14')
     * @param bool $smartMoney
     * @param bool $style
     * @param bool $onlytext
     * @return bool|string
     */
    public static function theEnd($count, $theend = [], $smartMoney = false, $style = false, $onlytext = false)
    {
        $count = ceil($count);

        if (is_string($theend)) {
            $theend = self::$theEnd[$theend];
        }

        $end = $count % 10;
        $end2 = $count / 10 % 10;

        if (isset($theend[3]) && $count == 0) {
            $ok = false;
            $count = false;

            if ($theend[3] != '') {
                $count = $theend[3];
            }
        } elseif ($end == 1 && $end2 != 1) {
            $ok = $theend[0];
        } elseif ($end > 1 && $end < 5 && $end2 != 1) {
            $ok = $theend[1];
        } else {
            $ok = $theend[2];
        }

        if ($smartMoney && $ok) {
            $count = self::money($count);
        }

        if ($style && $count != 0) {
            $count = '<span style="' . $style . '">' . $count . '</span>';
        }

        if ($ok && !$onlytext) $ok = '&nbsp;' . $ok;

        if ($onlytext) {
            return $ok;
        } else {
            return $count . $ok;
        }

    }

    public static function ruble($ruble = 0)
    {
        if (intval($ruble) != $ruble) {
            $rub = intval($ruble);
            $kop = ((float)$ruble - $rub) * 100;
        } else {
            $rub = intval($ruble);
            $kop = 0;
        }

        $str = '';

        if ($rub != 0) {
            $str .= self::theEnd($rub, 'rub');
        }

        if ($kop != 0) {
            if ($str) $str .= ' и&nbsp;';
            $str .= self::theEnd($kop, 'kop');
        }

        return $str;
    }

    public static function money($money = '0', $delim = '<i class="num_delim"> </i>')
    {
        if (!is_numeric($money)) return $money;

        $des = '';
        if (strpos($money, '.') !== false) {
            $des = '.' . str_replace('0.', '', round(($money - floor($money)), 2));
            $money = floor($money);
        }

        return str_replace(',', $delim, number_format($money)) . $des;
    }

    static public function toRomanic($integer)
    {

        $return = [];
        while ($integer > 0) {
            foreach (static::$romanicNumber as $rom => $arb) {
                if ($integer >= $arb) {
                    $integer -= $arb;
                    $return[] = $rom;
                    break;
                }
            }
        }

        return implode('', $return);
    }

    static public function romanicToNumber($romanic)
    {
        $result = 0;

        foreach (static::$romanicNumber as $key => $value) {
            while (strpos($romanic, $key) === 0) {
                $result += $value;
                $romanic = substr($romanic, strlen($key));
            }
        }

        return $result;
    }
}