<?php

namespace Spirit\Structure;

use Spirit\Engine;

class Component extends Basic
{
    /**
     * @var $this
     */
    protected static $instance;

    /**
     * @return $this
     */
    public static function getInstance()
    {
        if (!static::$instance) {
            static::$instance = static::make();
        }

        return static::$instance;
    }

    /**
     * @return $this|static
     */
    public static function make()
    {
        return new static();
    }

    public function view($view = null, $data = null)
    {
        if ($view[0] !== '/' && $view[0] !== '{') {
            $view = Engine::dir()->views_component . $view;
        }

        return parent::view($view, $data);
    }

    protected function getConfigPath($path)
    {
        if ($path !== '/') {
            $path = Engine::dir()->config_components . $path;
        }

        return parent::getConfigPath($path);
    }

    public function draw($view = null, $data = [])
    {
        return $this->view($view, $data);
    }
}