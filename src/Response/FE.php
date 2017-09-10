<?php

namespace Spirit\Response;

use Spirit\Config\Cfg;
use Spirit\Engine;

class FE
{
    use Cfg;

    protected static $meta = [];
    protected static $title;
    protected static $favicon;
    protected static $titleDescription;

    protected static $css_files = [];
    protected static $js_files = [];

    protected static $lineJS = [];

    public static function publicDir($path)
    {
        return '/' . $path;
    }

    public static function image($file)
    {
        if (strpos($file, '--static') === 0) {
            return str_replace("--static/", "/--static/images", $file);
        }

        return Engine::i()->url . 'images/' . $file;
    }

    public static function addCss($files, $first = false)
    {
        if (is_string($files)) {
            $files = [$files];
        }

        if ($first) {
            krsort($files);
        }

        foreach($files as $file) {
            if (in_array($file, self::$css_files))
                return;

            if ($first) {
                array_unshift(self::$css_files, $file);
            } else {
                self::$css_files[] = $file;
            }
        }
    }

    public static function addJs($files, $first = false)
    {
        if (is_string($files)) {
            $files = [$files];
        }

        if ($first) {
            krsort($files);
        }

        foreach($files as $file) {
            if (in_array($file, self::$js_files))
                return;

            if ($first) {
                array_unshift(self::$js_files, $file);
            } else {
                self::$js_files[] = $file;
            }

        }
    }

    public static function addLineScript($js, $first = false)
    {
        if ($first) {
            array_unshift(self::$lineJS, $js);
        } else {
            self::$lineJS[] = $js;
        }
    }

    public static function lineScript($withScriptTag = true, $type = 'text/javascript')
    {
        if (count(self::$lineJS) == 0) {
            return false;
        }

        $rtr = [];

        if ($withScriptTag) {
            if ($type) {
                $type = ' type="' . $type . '"';
            }

            $rtr[] = '<script' . $type . '>';
        }

        foreach(self::$lineJS as $j) {
            $rtr[] = $j;
        }

        if ($withScriptTag) {
            $rtr[] = '</script>';
        }

        return implode("\n", $rtr);
    }

    public static function scripts($js_arr = null, $ver = null)
    {
        if (!$js_arr) {
            $js_arr = static::$js_files;
        }

        if (is_string($js_arr)) {
            $js_arr = [$js_arr];
        }

        if (!$ver && isDebug()) {
            $ver = time();
        }

        $html = [];
        foreach($js_arr as $path) {
            $html[] = static::js($path, $ver);
        }

        return implode("\n\t", $html);
    }

    public static function js($path, $ver = null)
    {
        if (!$ver && isDebug()) {
            $ver = time();
        }

        if (strpos($path, '--static') === 0) {
            $path = str_replace("--static/", "/--static/js/", $path);
        } elseif (!preg_match("/^https?\:/i", $path)) {
            $path = Engine::i()->url . 'js/' . $path;
        }

        if ($ver) {
            $path .= (strpos($path, '?') !== false ? '&' : '?') . $ver;
        }

        return '<script type="text/javascript" src="' . $path . $ver . '"></script>';
    }

    public static function styles($css_arr = null, $ver = null)
    {
        if (!$css_arr) {
            $css_arr = static::$css_files;
        }

        if (is_string($css_arr)) {
            $css_arr = [$css_arr];
        }

        if (!$ver && isDebug()) {
            $ver = time();
        }

        $html = [];
        foreach($css_arr as $path) {
            $html[] = static::css($path, $ver);
        }

        return implode("\n\t", $html);
    }

    public static function css($path, $ver = null)
    {
        if (!$ver && isDebug()) {
            $ver = time();
        }

        if (strpos($path, '--static') === 0) {
            $path = str_replace("--static/", "/--static/css/", $path);
        } elseif (!preg_match("/^https?\:/i", $path)) {
            $path = Engine::i()->url . 'css/' . $path;
        }

        if ($ver) {
            $path .= (strpos($path, '?') !== false ? '&' : '?') . $ver;
        }

        return '<link rel="stylesheet" href="' . $path . '" type="text/css" />';
    }

    public static function addMeta($val)
    {
        static::$meta[] = $val;
    }

    public static function addMetaForMobile()
    {
        static::$meta[] = 'name="viewport" content="initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=yes"';
        static::$meta[] = 'http-equiv="content-type" content="application/xhtml+xml; charset=utf-8"';
        static::$meta[] = 'http-equiv="X-UA-Compatible" content="IE=edge"';
        static::$meta[] = 'name="MobileOptimized" content="176"';
    }

    public static function setTitleDescription($val)
    {
        static::$titleDescription = $val;
    }

    public static function title()
    {
        return static::$title;
    }

    public static function setTitle($val)
    {
        static::$title = $val;
    }

    public static function titleWithDescription($delimiter = ' | ')
    {
        if (static::$titleDescription) {
            return static::$title . $delimiter . static::$titleDescription;
        } else {
            return static::$title;
        }
    }

    public static function favicon($val = null)
    {
        if (!$val && !static::$favicon) {
            return null;
        }

        if (!$val) {
            $val = static::$favicon;
        }

        if (strpos($val, 'http') !== 0) {
            $val = Engine::i()->url . $val;
        }

        return '<link rel="shortcut icon" href="' . $val . '" />';
    }

    public static function setFavicon($val)
    {
        static::$favicon = $val;
    }

    public static function meta($meta = false)
    {
        if (!$meta)
            $meta = static::$meta;

        $html = [];
        foreach($meta as $__meta) {

            if (!is_string($__meta)) {
                $attr = [];
                foreach($__meta as $k => $v) {
                    $attr[] = $k . '="' . $v . '"';
                }
                $item = implode(' ', $attr);
            } else {
                $item = $__meta;
            }

            $html[] = '<meta ' . $item . '/>';
        }

        return implode("\n\t", $html);
    }
}