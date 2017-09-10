<?php

namespace Spirit\Structure;

use Spirit\Engine;

abstract class Service extends Basic
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
            $className = get_called_class();
            static::$instance = new $className;
        }

        return static::$instance;
    }

    public function view($view = null, $data = null)
    {
        if ($view[0] !== '/' && $view[0] !== '{') {
            $view = Engine::dir()->views_service . $view;
        }

        return parent::view($view, $data);
    }

    protected function getConfigPath($path)
    {
        if ($path !== '/') {
            $path = Engine::dir()->config_services . $path;
        }

        return parent::getConfigPath($path);
    }
}
