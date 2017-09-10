<?php

namespace Spirit\Cache;

use Spirit\Cache\File\Dir;
use Spirit\Config\DirConnected;
use Spirit\FileSystem;
use Spirit\Func\Date;

class File extends Store
{
    use DirConnected {
        DirConnected::dir as dirPath;
    }

    protected $defaultPath;

    public function has($key)
    {
        if (!$content = FileSystem::get($this->getPathKey($key))) {
            return false;
        }

        $t = mb_substr($content, 0, 10, "UTF-8");

        if ('0000000000' !== $t && (int)$t < time()) {
            return false;
        }

        return true;
    }

    protected function getPathKey($key_name)
    {
        $filename = sha1(SPIRIT_KEY . $key_name);

        if (strpos($key_name, '/') !== false) {
            $arr = explode('/', $key_name);
            $dirs = [];
            //$dirs[] = Engine::dir()->cache;
            $l = count($arr) - 1;
            for($i = 0; $i < $l; ++$i) {
                $dirs[] = $arr[$i];
            }
            $dir = implode('/', $dirs) . '/';
        } else {
            $dir = $this->defaultPath;
        }

        return $dir . substr($filename, 0, 2) . '/' . substr($filename, 2, 2) . '/' . $filename;
    }

    public function forever($key, $value)
    {
        $this->put($key, $value, null);
    }

    public function put($key, $value, $minutes = null)
    {
        $path = $this->getPathKey($key);

        if (is_null($minutes)) {
            $t = 9999999999;
        } elseif (!is_numeric($minutes)) {
            $t = Date::secondFromText($minutes);
        } elseif ($minutes < 0) {
            $t = -1;
        } else {
            $t = $this->toTimestamp($minutes);
        }

        if (isDebug()) {
            $d = debug_backtrace();
            if (!isset($d[0]['file'])) {
                $_file = $d[2]['file'];
                $_line = $d[2]['line'];
            } else {
                $_file = $d[0]['file'];
                $_line = $d[0]['line'];
            }

            $map = $_file . ':' . $_line;

            $this->statPut[] = [
                'key' => $key,
                'exp' => $t,
                'map' => $map
            ];
        }

        $content = $t . serialize($value);

        FileSystem::put($path, $content);
    }

    public function pull($key)
    {
        if ($content = $this->get($key)) {
            $this->forget($key);
            return $content;
        }

        return null;
    }

    public function get($key)
    {
        if (isDebug()) {
            $d = debug_backtrace();
            if (!isset($d[0]['file'])) {
                $_file = $d[2]['file'];
                $_line = $d[2]['line'];
            } else {
                $_file = $d[0]['file'];
                $_line = $d[0]['line'];
            }
            $map = $_file . ':' . $_line;

            $this->statGet[] = [
                'key' => is_array($key) ? implode(';', $key) : $key,
                'map' => $map
            ];
        }

        $path = $this->getPathKey($key);

        if (!$content = FileSystem::get($path)) {
            return null;
        }

        $t = mb_substr($content, 0, 10, "UTF-8");

        if ('0000000000' === $t) {
            FileSystem::delete($path);
        } elseif ((int)$t < time()) {
            return null;
        }

        return unserialize(mb_substr($content, 10, null, "UTF-8"));
    }

    public function forget($key)
    {
        $path = $this->getPathKey($key);

        if (FileSystem::get($path)) {
            FileSystem::delete($path);
        }
    }

    protected function init()
    {
        if (strpos($this->config['path'], '/') === 0) {
            $this->defaultPath = $this->config['path'];
        } else {
            $this->defaultPath = static::dirPath()->storage . $this->config['path'];
        }
    }

    /**
     * @param string $dir
     * @return Dir
     */
    public function dir($dir)
    {
        if (substr($dir, -1) !== '/') {
            $dir .= '/';
        }

        if (mb_substr($dir, 0, 1, "UTF-8") === '/') {
            $dir = mb_substr($dir, 1, null, "UTF-8");
        }

        $dir = $this->defaultPath . $dir;

        return Dir::make($this, $dir);
    }

    public function flush()
    {
        FileSystem::removeDirectory($this->defaultPath);
    }
}