<?php

namespace Spirit\Cache\File;

use Spirit\Cache\File;
use Spirit\Engine;
use Spirit\FileSystem;

/**
 * Class Dir
 * @package Spirit\Cach\File
 *
 * @method mixed get($key)
 * @method mixed pull($key)
 * @method mixed put($key, $value, $exp = 300)
 * @method mixed forever($key, $value)
 * @method mixed forget($key)
 * @method mixed has($key)
 */
class Dir
{

    /**
     * @var Dir[]
     */
    protected static $instance = [];

    /**
     * @param File $fileCache
     * @param $dir
     * @return Dir
     */
    public static function make(File $fileCache, $dir)
    {
        if (!isset(static::$instance[$dir])) {
            static::$instance[$dir] = new static($fileCache, $dir);
        }

        return static::$instance[$dir];
    }

    protected $dir = null;

    /**
     * @var null|File
     */
    protected $fileCache = null;

    public function __construct(File $fileCache, $dir)
    {
        $this->fileCache = $fileCache;
        $this->dir = $dir;
    }

    public function __call($method, $arr)
    {
        $arr[0] = $this->dir . $arr[0];

        return $this->fileCache->{$method}(...$arr);
    }

    public function flush()
    {
        FileSystem::removeDirectory($this->dir);
    }
}