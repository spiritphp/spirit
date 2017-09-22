<?php

namespace Spirit;

use Spirit\Config\Cfg;
use Spirit\Error\Abort;
use Spirit\Error\HttpException;
use Spirit\Error\Info;
use Spirit\Error\LogView;
use Spirit\Error\LogWriter;
use Spirit\Error\Pause;
use Spirit\Request\Client;
use Spirit\Request\Session;

class Error
{
    use Abort, Pause, Cfg;

    protected $disableLog = false;

    protected $headers;
    protected $message;
    protected $statusCode;
    protected $file;
    protected $line;
    protected $trace;
    /**
     * @var Info
     */
    protected $info;
    protected $isLogExist;

    public function __construct()
    {
        set_error_handler([$this, 'handlerError']);
        set_exception_handler([$this, 'handlerErrorObject']);
    }

    public function handlerError($errno, $errstr = null, $errfile = null, $errline = null)
    {
        if (!Engine::i()->isConsole && !Engine::i()->isDebug) {
            exit();
        }

        $nextString = Engine::i()->isConsole ? "\n" : '<br/>';

        echo 'ERROR' . $nextString;
        echo '-----' . $nextString;
        echo 'CODE: ' . $errno . $nextString;
        echo 'MESSAGE: ' . $errstr . $nextString;
        echo 'FILE: ' . $errfile . ':' . $errline;
        exit();
    }

    /**
     * @param \Error $error
     */
    public function handlerErrorObject($error)
    {
        $this->handlerError($error->getCode(), $error->getMessage(), $error->getFile(), $error->getLine());
    }
    /**
     * @param \Error|HttpException $error
     */
    public static function makeFromObject($error)
    {

        $e = (new Error())->statusCode($error->getCode())
            ->message($error->getMessage())
            ->file($error->getFile())
            ->line($error->getLine())
            ->trace($error->getTrace())
            ;

        if (method_exists($error,'getHeaders')) {
            $e->headers($error->getHeaders());
        }

        $e->complete();
    }

    public static function make($statusCode, $message = null, $file = null, $line = null, $trace = null)
    {
        if (!$file) {
            $d = debug_backtrace();
            $file = isset($d[1]['file']) ?  $d[1]['file'] : $d[0]['file'];
            $line = isset($d[1]['line']) ?  $d[1]['line'] : $d[0]['line'];
        }

        (new Error())->statusCode($statusCode)
            ->message($message)
            ->file($file)
            ->line($line)
            ->trace($trace)
            ->complete();
    }

    /**
     * @param $v
     * @return $this|static
     */
    public function statusCode($v)
    {
        $this->statusCode = $v;

        return $this;
    }

    /**
     * @param $v
     * @return $this|static
     */
    public function line($v)
    {
        $this->line = $v;

        return $this;
    }

    /**
     * @param $v
     * @return $this|static
     */
    public function file($v)
    {
        $this->file = $v;

        return $this;
    }

    /**
     * @param $v
     * @return $this|static
     */
    public function message($v)
    {
        $this->message = $v;

        return $this;
    }

    /**
     * @param $v
     * @return $this|static
     */
    public function trace($v)
    {
        $this->trace = $v;

        return $this;
    }

    /**
     * @param $v
     * @return $this|static
     */
    public function headers($v)
    {
        $this->headers = $v;

        return $this;
    }

    public function disableLog($v = true)
    {
        $this->disableLog = $v;

        return $this;
    }

    public function complete()
    {
        $info = $this->info = new Info();

        $info->status_code = $this->statusCode;
        $info->message = $this->message;
        $info->file = $this->file;
        $info->line = $this->line;
        $info->headers = $this->headers;

        $info->route = Route::current();
        $info->cookie = json_encode($_COOKIE, JSON_UNESCAPED_UNICODE);
        $info->ip = Client::getIP();
        $info->user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $info->query_string = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
        $info->request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        $info->referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        $info->time_script = Engine::getTotalTimeLine(). ' s';
        $info->memory_top = memory_get_peak_usage() . ' bytes';
        $info->memory_now = memory_get_usage() . ' bytes';

        if (Auth::check()) {
            $info->user = Auth::id();
        }

        $this->initTrace($this->trace ? $this->trace : debug_backtrace());

        if (static::cfg()->error['log'] && $this->disableLog === false) {
            $this->log();
        }

        Session::complete();

        $this->show();
    }

    protected function initTrace($d)
    {
        $dcount = count($d) + 1;
        $dtr = [];
        $traceFull = [];
        foreach($d as $dk => $dv) {
            --$dcount;

            if ($dk == 0) {
                continue;
            }

            //dd($dv);

            if (isset($dv['args']) && is_array($dv['args']) && count($dv['args'])) {
                $arg = json_encode($dv['args'], JSON_UNESCAPED_UNICODE);
                //$arg = implode(', ', $dv['args']);
            } else {
                $arg = '';
            }

            $__dop = '';
            if (isset($dv['class'], $dv['function'])) {
                if (strpos($dv['class'],'Spirit\Error') !== false) continue;
                if (strpos($dv['function'],'handlerError') !== false) continue;
                if (strpos($dv['function'],'errorFire') !== false) continue;

                $__dop = $dv['class'] . $dv['type'] . $dv['function'] . '(' . $arg . ')';
            } elseif($dv['function']) {
                $__dop = $dv['function'] . '(' . $arg . ')';
            }

            if (!isset($dv['file'])) {
                $dv['file'] = null;
            }

            if (!isset($dv['line'])) {
                $dv['line'] = null;
            }

            $dv['file'] = str_replace(Engine::dir()->abs_path, '', $dv['file']);

            $traceFull[$dcount] = [
                'file' => $dv['file'],
                'line' => $dv['line'],
                'trace' => $__dop
            ];

            $dtr[$dcount] =
                $dv['file'] . ':' . $dv['line'] . "\n" .
                str_repeat(' ',strlen($dcount) + 2) .
                $__dop;
        }

        $this->info->trace = $dtr;
        $this->info->traceFull = $traceFull;
    }

    protected function log()
    {
        LogWriter::make($this->info)->write();
    }

    protected function show()
    {
        LogView::make($this->info)->render();
        exit();
    }


}