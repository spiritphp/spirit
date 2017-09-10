<?php

namespace Spirit\Error;

use Spirit\Error;

/**
 * Trait Pause
 * @package Spirit\Error
 * @mixin Error
 */
trait Pause {

    public static function pause($message = null)
    {
        (new static())->statusCode(503)
            ->message($message)
            ->headers(['Retry-After' => '3600'])
            ->disableLog()
            ->complete()
        ;
    }

}