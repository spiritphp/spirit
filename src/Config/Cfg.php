<?php

namespace Spirit\Config;

use Spirit\Config;
use Spirit\Engine;

trait Cfg {

    /**
     * @return Config
     */
    protected static function cfg()
    {
        return Engine::cfg();
    }

}