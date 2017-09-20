<?php

namespace Spirit\Auth\Services;

use Spirit\Config\Cfg;

abstract class Service {

    use Cfg;

    public static function make()
    {
        return static();
    }

    /**
     * @return \Spirit\Common\Models\User
     */
    public static function userModel()
    {
        return static::cfg()->userModel;
    }
}