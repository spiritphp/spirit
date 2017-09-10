<?php

namespace Spirit\Structure;

use Spirit\Engine;

abstract class Command
{
    protected $description = null;
    protected $descriptionCommands = null;


    protected $time;
    protected $callback = false;
    protected $isInfo = false;
    protected $args = [];
    protected $extCommand;

    public function __construct($args = [], $ext_command = null)
    {
        $this->time = time();
        $this->args = $args;
        $this->extCommand = $ext_command;
    }

    /**
     * @return \Spirit\Config
     */
    protected function cfg()
    {
        return Engine::cfg();
    }

    /**
     * @return array
     */
    public static function findAppCommands()
    {
        $classes = [];
        foreach (glob(Engine::i()->abs_path . "app/Commands/*.php") as $filename) {
            $classes[] =  basename($filename,'.php');
        }

        return $classes;
    }

    /**
     * @return array
     */
    public static function findDefaultCommands()
    {
        $classes = [];
        foreach (glob(Engine::i()->spirit_path . "Console/Commands/*.php") as $filename) {

            $f = basename($filename,'.php');

            if ($f === 'Cron' || $f === 'Help') continue;

            $classes[] = $f;
        }

        return $classes;
    }

    public function setIsInfo()
    {
        return $this->isInfo = true;
    }

    protected function arg($key)
    {
        if (!isset($this->args[$key])) return null;

        return $this->args[$key];
    }

    protected function getFirstBoolArg()
    {
        foreach ($this->args as $key => $value) {
            if ($value === true) {
                return $key;
            }
        }

        return null;
    }

    public function exec()
    {
        $this->command();
    }

    abstract protected function command();

     public function getDescription()
    {
        return $this->description;
    }

    public function getDescriptionCommands()
    {
        return $this->descriptionCommands;
    }
}