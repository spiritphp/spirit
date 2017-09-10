<?php

namespace Spirit\Func;

class Date
{

    public static $day = [
        -2 => 'Послезавтра',
        -1 => 'Завтра',
        0 => 'Сегодня',
        1 => 'Вчера',
        2 => 'Позавчера',
    ];

    public static $week = [
        0 => 'Воскресение',
        1 => 'Понедельник',
        2 => 'Вторник',
        3 => 'Среда',
        4 => 'Четверг',
        5 => 'Пятница',
        6 => 'Суббота',
    ];

    public static $textForSecond = [
        'minute' => 60,
        'hour' => 3600,
        'day' => 86400,
        'week' => 604800,
        'month' => 2592000,
        'year' => 31536000
    ];

    public static $last_time = [];

    public static function timeStart($key)
    {
        return self::$last_time[$key] = microtime(true);
    }

    public static function timeEnd($key, $echo = false)
    {
        if ($echo) {
            echo '<div>' . (microtime(true) - self::$last_time[$key]) . '</div>';
            return false;
        } else {
            return (microtime(true) - self::$last_time[$key]);
        }
    }

    /**
     * Дата из секунд
     *
     * @param $second
     * @return string
     */
    public static function fromSecond($second)
    {
        $second = round($second);
        $s = $second % 60;
        $minute = ($second - $s) / 60;
        $m = $minute % 60;
        $hour = ($minute - $m) / 60;
        $h = $hour % 24;
        $d = ($hour - $h) / 24;

        $date['day'] = Num::theEnd($d, 'D');
        $date['hour'] = Num::theEnd($h, 'H');
        $date['minute'] = Num::theEnd($m, 'min_1');
        $date['second'] = Num::theEnd($s, 'sec_1');

        $t = [];

        $count = 0;
        foreach ($date as $k => $i) {

            if ($k == 'minute' && $date['day']) continue;

            if ($k == 'second' && ($date['hour'] || $date['day'] || $minute >= 5)) {
                continue;
            }

            if ($k == 'minute' && $m >= 5) {
                $i = Num::theEnd($m + ceil($s / 60), 'min_1');
            }

            if ($i) {
                if (count($t) != 0 && $count == 1) {
                    $i = ' и&nbsp;' . $i;
                } elseif (count($t) != 0) {
                    $i = ' ' . $i;
                }

                ++$count;

                $t[$k] = $i;
            }
        }

        if (count($t) == 0) $t[] = 'мгновение';

        return implode('', $t);
    }

    public static function shortFromSecond($second)
    {
        if ($second < 120) {
            return $second . '&nbsp;сек';
        }

        $minute = ceil($second / 60);
        $hour = ceil($second / 3600);

        if ($minute < 55) {
            return $minute . '&nbsp;мин';
        }

        return $hour . '&nbsp;ч';

    }

    public static function smallFromSecond($second)
    {
        $second = round($second);
        $s = $second % 60;
        $minute = floor(($second - $s) / 60);
        $m = $minute % 60;
        $hour = floor(($minute - $m) / 60);
        $h = $hour % 24;
        $d = floor(($hour - $h) / 24);

        $date['day'] = $d > 0 ? $d . '&thinsp;д' : false;
        $date['hour'] = $h > 0 ? $h . '&thinsp;ч' : false;
        $date['minute'] = $m > 0 ? $m . '&thinsp;м' : false;
        $date['second'] = $s > 0 ? $s . '&thinsp;с' : false;

        $t = [];

        $count = 0;
        foreach ($date as $k => $i) {

            if ($k == 'minute' && $date['day']) continue;

            if ($k == 'second' && ($date['hour'] || $date['day'] || $m >= 5)) {
                continue;
            }

            if ($k == 'minute' && $m >= 5) {
                $i = ($m + ceil($s / 60)) . '&thinsp;м';
            }

            if ($i) {
                if (count($t) != 0 && $count == 1) {
                    $i = ' и&nbsp;' . $i;
                } elseif (count($t) != 0) {
                    $i = ' ' . $i;
                }

                ++$count;

                $t[$k] = $i;
            }
        }

        if (count($t) == 0) $t[] = 'мгновение';

        return implode('', $t);
    }

    // Возвращает написание по времени (завтра, послезавтра, вчера и т.д.)
    public static function dayToText($time)
    {
        $info = getdate($time);
        $c_info = getdate(time());

        $diff = $info['yday'] - $c_info['yday'];

        if (isset(static::$day[$diff])) {
            return static::$day[$diff];
        } else {
            return static::$week[$info['wday']];
        }

    }

    // Возвращает секунды
    public static function secondFromText($text)
    {
        if (isset(static::$textForSecond[$text])) {
            return static::$textForSecond[$text];
        }

        return 0;
    }

    public static function pretty($text_date)
    {
        if (!$text_date) return '';

        $time = strtotime($text_date);
        $now = time();
        $period = round($now - $time);

        if ($period < 60) {
            if ($period == 0) return 'Только что';
            $new_date = Num::theEnd($period, array('секунду', 'секунды', 'секунд')) . ' назад';

        } elseif (round($period / 60) < 60) {
            $new_date = Num::theEnd(round($period / 60), array('минуту', 'минуты', 'минут')) . ' назад';
        } else {
            $_year = date("Y", $time);
            $_month = date("m", $time);
            $_day = date("j", $time);
            $_date = date("j.m.Y", $time);
            $_clock = date("H:i", $time);

            $yesterday = date("j.m.Y", time() - 86400);
            $today = date("j.m.Y", time());
            $nowYear = date("Y");

            if ($yesterday === $_date) {
                $new_date = str_replace($yesterday, 'Вчера', $_date);
            } elseif ($today === $_date) {
                $new_date = str_replace($today, 'Сегодня', $_date);
            } else {
                $new_date = $_day . '&nbsp;' . static::russianMonth($_month, false);
                if ($nowYear !== $_year) {
                    $new_date .= '&nbsp;' . $_year;
                }
            }

            $new_date .= '&nbsp;в&nbsp;' . $_clock;
        }

        return $new_date;
    }

    public static function russianMonth($month, $ucfirst = true)
    {
        $month = intval($month);

        $russianMonth = [
            'Января', 'Февраля', 'Марта',
            'апреля', 'мая', 'Июня',
            'Июля', 'Августа', 'Сентября',
            'Октября', 'Ноября', 'Декабря'
        ];

        $textMonth = $russianMonth[$month - 1];

        return !$ucfirst ? mb_strtolower($textMonth, "UTF-8") : $textMonth;
    }

}