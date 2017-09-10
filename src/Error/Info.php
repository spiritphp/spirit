<?php

namespace Spirit\Error;

use Spirit\Route;
use Spirit\Structure\Box;

/**
 * Class Info
 * @package Spirit\Error
 *
 * @property string|integer $status_code
 * @property string|integer $message
 * @property string|integer $file
 * @property string|integer $line
 * @property array $headers
 * @property string|integer $date
 * @property string|integer $problem
 * @property Route\Current $route
 * @property string|integer $user
 * @property string|integer $cookie
 * @property string|integer $ip
 * @property string|integer $query_string
 * @property string|integer $request_uri
 * @property string|integer $referer
 * @property string|integer $user_agent
 * @property string|integer $time_script
 * @property string|integer $memory_top
 * @property string|integer $memory_now
 * @property array $trace
 * @property array $traceFull
 * @property array $debug_backtrace
 */
class Info extends Box {

    public function __construct($data = [])
    {
        parent::__construct($data);

        $this->date = date('d.m.Y, H:i:s');
    }

    public function __set($k, $v)
    {
        $value = parent::__set($k,$v);

        return $value;
    }

}