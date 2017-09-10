<?php

namespace Spirit\Error;

use Spirit\Error;

/**
 * Trait Abort
 * @package Spirit\Error
 * @mixin Error
 */
trait Abort {

    public static function abort($statusCode, $message = '', $headers = [])
    {
        throw new HttpException($statusCode, $message, $headers);
    }

}