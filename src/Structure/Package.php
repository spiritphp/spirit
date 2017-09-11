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


    protected function copy($src, $dest)
    {
        if (is_array($src)) {
            foreach($src as $_src) {
                $this->copy($_src, $dest);
            }

            return;
        }

        $src_parts = pathinfo($src);
        if (!isset($src_parts['extension'])) {
            FileSystem::copyDirectory($src, $dest);
            return;
        }

        $dest_parts = pathinfo($dest);
        if (!isset($dest_parts['extension'])) {
            $dest = $dest . basename($src);
        }

        FileSystem::copy($src, $dest);
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