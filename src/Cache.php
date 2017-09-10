<?php

namespace Spirit;

use Spirit\Cache\Memcached;
use Spirit\Cache\File;
use Spirit\Cache\Store;
use Spirit\Config\Cfg;

/**
 * Class Cache
 * @package Spirit
 *
 * @method static bool has(string $key)
 * @method static mixed get(string $key)
 * @method static void put(string $key, $value, $minutes)
 * @method static mixed pull(string $key)
 * @method static void forever(string $key, $value)
 * @method static void forget(string $key)
 *
 */
class Cache
{
    use Cfg;

    const DRIVER_FILE = 'file';
    const DRIVER_MEMCACHED = 'memcached';

    protected static $stores = [];

    /**
     * @var Store[]
     */
    protected static $drivers = [
        self::DRIVER_FILE => File::class,
        self::DRIVER_MEMCACHED => Memcached::class,
    ];

    public function __construct()
    {

    }

    /**
     * @param null $name
     * @return Store|File|Memcached
     * @throws \Exception
     */
    public static function store($name = null)
    {
        if (is_null($name)) {
            $name = static::cfg()->cache['default'];
        }

        if (isset(static::$stores[$name])) {
            return static::$stores[$name];
        }

        if (!isset(static::cfg()->cache['stores'][$name])) {
            throw new \Exception('Cache «' . $name . '» is not found');
        }

        $opt = static::cfg()->cache['stores'][$name];

        $className = static::$drivers[$opt['driver']];

        return static::$stores[$name] = $className::make($opt);
    }

    /**
     * @param $method
     * @param array $args
     * @return mixed
     */
    public static function __callStatic($method, array $args = [])
    {
        $class = static::store();

        return call_user_func_array([$class, $method], $args);
    }
}