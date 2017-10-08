<?php

namespace Spirit\Structure;

use Spirit\Engine;
use Spirit\Func;
use Spirit\View;

abstract class Basic
{
    protected $config = null;
    protected $loadingConfig = [];
    protected $defaultConfig = [];

    public function __construct()
    {
        if ($this->config) {
            $this->loadConfig();
        } elseif (count($this->defaultConfig)) {
            $this->loadingConfig = $this->defaultConfig;
        }
    }

    /**
     * @param null|mixed $view
     * @param null|mixed $data
     * @return View|string
     */
    protected function view($view = null, $data = null)
    {
        return View::make($view, $data);
    }

    protected function getConfigPath($path)
    {

        if($path !== '/') {
            $path = Engine::dir()->config_app . $path;
        }

        if (!$ext = pathinfo($path, PATHINFO_EXTENSION)) {
            $path .= '.php';
        }

        return $path;
    }

    protected function loadConfig()
    {
        $file_path = $this->getConfigPath($this->config);

        Func\Date::timeStart('config');
        $cfg = null;
        if (file_exists($file_path)) {
            $cfg = Engine::i()->includeFile($file_path);
        }
        $t = Func\Date::timeEnd('config');

        if (isDebug()) {
            Engine::cfg()->allconfig[] = array(
                'type' => get_called_class(),
                'path' => $file_path . ($cfg ? '' : ' [NOT FOUND]'),
                'class' => get_class($this),
                'time' => $t
            );
        }

        if (is_null($cfg)) {
            $cfg = $this->defaultConfig;
        }

        return $this->loadingConfig = $cfg;
    }

    protected function c($k)
    {
        $cfg = $this->cfg();

        return isset($cfg[$k]) ? $cfg[$k] : null;
    }

    protected function cfg()
    {
        return $this->loadingConfig;
    }

}
