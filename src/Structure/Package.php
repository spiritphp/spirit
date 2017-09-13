<?php

namespace Spirit\Structure;

use Spirit\Console;
use Spirit\Engine;
use Spirit\FileSystem;

abstract class Package
{

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

    public static function getClassName()
    {
        return get_called_class();
    }

    abstract public function install();

    protected function packageFolder($to)
    {
        return 'packages/' . static::$name . '/' . $to;
    }

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

            if (!is_dir($src)) {
                throw new \Exception('Not found dir: ' . $src);
            }

            if (is_dir($dest)) {
                FileSystem::removeDirectory($dest);
            }

            echo '=> ' . str_replace(Engine::i()->abs_path,'',$dest) . "\n";
            FileSystem::copyDirectory($src, $dest);
            return;
        }

        $dest_parts = pathinfo($dest);
        if (!isset($dest_parts['extension'])) {
            $dest = $dest . basename($src);
        }

        if (is_file($dest)) {
            FileSystem::delete($dest);
        }

        echo '=> ' . str_replace(Engine::i()->abs_path,'',$dest) . "\n";
        FileSystem::copy($src, $dest);
    }

    protected function copyConfig($src, $to = null)
    {
        if (is_null($to)) {
            $to = static::$name . '.php';
        }

        $dest = Engine::dir()->config . 'packages/' . $to;
        $this->copy($src, $dest);
    }

    protected function copyAssets($src, $to = '')
    {
        $dest = Engine::dir()->abs_path . 'resources/assets/' . $this->packageFolder($to);
        $this->copy($src, $dest);
    }

    protected function copyAssetsScss($src, $to = '')
    {
        $dest = Engine::dir()->abs_path . 'resources/assets/scss/' . $this->packageFolder($to);
        $this->copy($src, $dest);
    }

    protected function copyAssetsJs($src, $to = '')
    {
        $dest = Engine::dir()->abs_path . 'resources/assets/js/' . $this->packageFolder($to);
        $this->copy($src, $dest);
    }

    protected function copyPublic($src, $to = '')
    {
        $dest = Engine::dir()->public . $this->packageFolder($to);
        $this->copy($src, $dest);
    }

    protected function copyPublicCss($src, $to = '')
    {
        $dest = Engine::dir()->public . 'css/' . $this->packageFolder($to);
        $this->copy($src, $dest);
    }

    protected function copyPublicJs($src, $to = '')
    {
        $dest = Engine::dir()->public . 'js/' . $this->packageFolder($to);
        $this->copy($src, $dest);
    }

    protected function copyView($src, $to = '')
    {
        $dest = Engine::dir()->views . $this->packageFolder($to);
        $this->copy($src, $dest);
    }
}