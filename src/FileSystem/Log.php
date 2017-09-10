<?php

namespace Spirit\FileSystem;

use Spirit\Engine;
use Spirit\FileSystem;

/**
 * Class Log
 * @package Spirit\FileSystem
 *
 * @method static void error($str, $dir = null)
 * @method static void info($str, $dir = null)
 * @method static void success($str, $dir = null)
 * @method static void warning($str, $dir = null)
 * @method static void debug($str, $dir = null)
 */
class Log
{

    public static function w($str, $dir = null)
    {
        $filename = date('Y_m_d') . '.log';

        if ($dir) {
            if (substr($dir, -1) !== '/') {
                $dir .= '/';
            }

            if (substr($dir, 0, 1) === '1') {
                $dir = substr($dir, 1);
            }
        } else {
            $dir = '';
        }

        $file_path = Engine::dir()->logs . $dir . $filename;

        FileSystem::put($file_path, $str, true);
    }

    protected static function log($type, $str, $dir = null)
    {
        if (is_array($str)) {
            $str = json_encode($str, JSON_UNESCAPED_UNICODE);
        }

        $time = '[' . date("H:i:s") . ']';

        static::w($time . ' ' . $type . ': ' . $str, $dir);
    }

    public static function __callStatic($name, $arguments)
    {
        static::log($name, $arguments[0], (isset($arguments[1]) ? $arguments[1] : null));
    }
}