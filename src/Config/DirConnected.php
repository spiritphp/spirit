<?php

namespace Spirit\Config;

use Spirit\Engine;

trait DirConnected {

    /**
     * @return Dir
     */
    protected static function dir()
    {
        return Engine::dir();
    }

}