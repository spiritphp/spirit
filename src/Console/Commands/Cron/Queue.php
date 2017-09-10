<?php

namespace Spirit\Console\Commands\Cron;

class Queue
{

    public static function make($name)
    {
        return new static($name);
    }

    protected $name;
    protected $callback;
    protected $cron;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function add($callback)
    {
        $this->callback = $callback;
        return $this;
    }

    public function cron($cron)
    {
        $this->cron = $cron;
        return $this;
    }
}