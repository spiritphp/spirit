<?php

namespace Spirit\Structure;

abstract class Plugin {

    /**
     * @return static
     */
    public static function make()
    {
        return new static();
    }

    public function __construct()
    {

    }

    public function init()
    {
        $this->boot();
    }

    abstract protected function boot();
}