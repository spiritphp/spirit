<?php

namespace Spirit\Func;

use Spirit\Engine;

class Trace
{

    protected static $traceAfter = [];

    public static function after($data, $vardump = false, $color = true)
    {
        static::$traceAfter[] = static::it($data, $vardump, true, $color, debug_backtrace());
    }

    /**
     * Трейсер
     *
     * @param $data
     * @param bool $vardump
     * @param bool $return
     * @param bool $htmlAndColor
     * @param mixed $d
     * @return bool|mixed|string
     */
    public static function it($data, $vardump = false, $return = false, $htmlAndColor = true, $d = false)
    {
        if (!isDebug() && !(Engine::i()->isConsole || Engine::i()->isTesting)) {
            return false;
        }

        ob_start();

        if ($vardump) {
            var_dump($data);
        } else {
            print_r($data);
        }

        $trace = ob_get_contents();
        ob_end_clean();

        if (Engine::i()->isConsole) {
            $htmlAndColor = false;
        }

        if ($htmlAndColor) {
            $trace = str_replace('&lt;?&nbsp;', '', highlight_string('<? ' . $trace, true));
        }

        $d = $d ? $d : debug_backtrace();

        $_file = '--';
        $_line = '--';
        foreach($d as $d_item) {
            if (!isset($d_item['file']))
                continue;

            if (strpos($d_item['file'], 'Trace') !== false)
                continue;

            if (strpos($d_item['file'], 'helper') !== false)
                continue;

            $_file = $d_item['file'];
            $_line = $d_item['line'];

            break;
        }


        if ($htmlAndColor) {
            $trace = '<small class="tracer-small">' . $_file . '__' . $_line . '</small><div class="tracer-code">' . $trace . '</div>';
        } else {
            $trace = $_file . '__' . $_line . "\n\n" . $trace;
        }

        if (!$return) {
            echo($htmlAndColor ? '<pre class="tracer">' . $trace . '</pre>' : $trace);

            return null;
        } else {
            return $trace;
        }
    }

    public static function map($return = false, $html = true)
    {
        $d = debug_backtrace();
        $_file = isset($d[1]['file']) ? $d[1]['file'] : __FILE__;
        $_line = isset($d[1]['line']) ? $d[1]['line'] : __LINE__;

        $map = [
            'file' => $_file,
            'line' => $_line
        ];

        $_file = $d[0]['file'];
        $_line = $d[0]['line'];

        if (Engine::i()->isConsole) {
            $html = false;
        }

        if ($html) {
            $trace = '<small>' . $_file . '__' . $_line . '</small><br/>' . implode(':', $map);
        } else {
            $trace = $_file . '__' . $_line . "\n" . implode(':', $map);
        }

        if (!$return) {
            if ($html) {
                echo '<pre class="tracer">' . $trace . '</pre>';
            } else {
                echo $trace;
            }
            return null;
        } else {
            return $map;
        }
    }

    public static function getTraceAfter()
    {
        return static::$traceAfter;
    }
}