<?php

namespace Spirit;

use Spirit\Auth\DefaultDriver;
use Spirit\Auth\Driver;
use Spirit\Common\Models\User;

/**
 * Class Auth
 * @package Spirit
 *
 * @method static void init()
 * @method static boolean check()
 * @method static boolean guest()
 * @method static integer id()
 * @method static User user()
 * @method static void loginById($id, $remember = false)
 * @method static User|null authorize($filters, $remember = false)
 * @method static User|null register($fields, $autoAuthorize = true, $remember = false)
 * @method static void logout()
 * @method static void setUserCookie($user, $version = null)
 *
 */
class Auth {

    /**
     * @var Driver[]
     */
    public static $storage = [];

    public static $defaultDriver = DefaultDriver::class;

    /**
     * @param null $className
     * @return Driver
     */
    public static function driver($className = null)
    {
        if (is_null($className)) {
            $className = Engine::cfg()->auth['driver'] ? Engine::cfg()->auth['driver'] : static::$defaultDriver;
        }

        if (isset(static::$storage[$className])) {
            return static::$storage[$className];
        }

        return static::$storage[$className] = new $className();
    }

    public static function __callStatic($name, $arguments)
    {
        if (Engine::cfg()->auth['init'] === false) {
            return null;
        }

        return static::driver()->{$name}(...$arguments);
    }

}