<?php

namespace Spirit;

use Spirit\Config\Cfg;
use Spirit\Structure\Plugin;

class Plugins {

    use Cfg;

    public static function init()
    {
        foreach (static::cfg()->plugins as $item) {
            /**
             * @var Plugin $item
             */
            $provider = $item::make();
            $provider->init();
        }
    }

}