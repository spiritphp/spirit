<?php

namespace Spirit\Structure;

use Spirit\Engine;
use Spirit\FileSystem;

abstract class Package {

    protected static $name;

    protected static $description;

    public static function name()
    {
        return static::$name;
    }

    public static function description()
    {
        return static::$description;
    }

    public static function getClassName() {
        return get_called_class();
    }

    abstract public function install();


    protected function copy($cfg, $dest)
    {
        if (is_array($cfg)) {
            foreach($cfg as $_cfg) {
                $this->copy($_cfg, $dest);
            }

            return;
        }

        $path_parts = pathinfo($dest);

        if (!isset($path_parts['extension'])) {
            $dest = $dest . basename($cfg);
        }

        $dest_dir = dirname($dest);

        if (!is_dir($dest_dir)) {
            mkdir($dest_dir, 0777, true);
        }

        FileSystem::copy($cfg, $dest);
    }

    protected function copyConfig($src, $to = '')
    {
        $dest = Engine::dir()->config . 'packages/' . $to;
        $this->copy($src, $dest);
    }

    protected function copyAssets($src, $to = '')
    {
        $dest = Engine::dir()->abs_path . 'resources/assets/' . $to;
        $this->copy($src, $dest);
    }

    protected function copyAssetsScss($src, $to = '')
    {
        $dest = Engine::dir()->abs_path . 'resources/assets/scss/' . $to;
        $this->copy($src, $dest);
    }

    protected function copyAssetsJs($src, $to = '')
    {
        $dest = Engine::dir()->abs_path . 'resources/assets/js/' . $to;
        $this->copy($src, $dest);
    }

    protected function copyPublic($src, $to = '')
    {
        $dest = Engine::dir()->public . $to;
        $this->copy($src, $dest);
    }

    protected function copyPublicCss($src, $to = '')
    {
        $dest = Engine::dir()->public . 'css/' . $to;
        $this->copy($src, $dest);
    }

    protected function copyPublicJs($src, $to = '')
    {
        $dest = Engine::dir()->public . 'js/' . $to;
        $this->copy($src, $dest);
    }

    protected function copyView($src, $to = '')
    {
        $dest = Engine::dir()->public . 'views/' . $to;
        $this->copy($src, $dest);
    }
}