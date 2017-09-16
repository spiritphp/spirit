<?php

namespace Spirit\Structure;

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

    /**
     * @var array
     */
    protected $args = [];

    public function __construct($args)
    {
        $this->args = $args;
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

    protected function toPackage($to = '')
    {
        return 'packages/' . static::$name . '/' . $to;
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
        $dest = Engine::dir()->views . $to;
        $this->copy($src, $dest);
    }

    protected function copyController($src, $to = '')
    {
        $dest = Engine::dir()->abs_path . 'app/Controllers/' . $to;
        $this->copy($src, $dest);
    }

    protected function copyModel($src, $to = '')
    {
        $dest = Engine::dir()->abs_path . 'app/Models/' . $to;
        $this->copy($src, $dest);
    }

    protected function copyMigration($src, $to = '')
    {
        $dest = Engine::dir()->abs_path . 'migrations/' . $to;
        $this->copy($src, $dest);
    }

    protected function copyRoute($src, $to = '')
    {
        $dest = Engine::dir()->routes . $to;
        $this->copy($src, $dest);
    }
}