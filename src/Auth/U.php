<?php

namespace Spirit\Auth;

use Spirit\Auth;

/**
 * Class U
 * @package Spirit
 *
 * @property array $roles
 * @property integer $user_id
 * @property integer $login
 * @property integer $token
 * @property integer $email
 * @property integer $ip
 */
abstract class U
{
    const ROLE_ROOT = 'root';
    const ROLE_PANEL = 'panel';
    const ROLE_USER_ACL = 'user_acl';
    const ROLE_USERS = 'users';
    const ROLE_PAGE = 'page';
    const ROLE_CLEAR = 'clear';

    public static $roleDescriptions = [
        self::ROLE_ROOT => 'Root'
        , self::ROLE_PANEL => 'Панель управления'
        , self::ROLE_USER_ACL => 'Устанавливать права пользователям'
        , self::ROLE_USERS => 'Пользователи'
        , self::ROLE_PAGE => 'Страницы'
        , self::ROLE_CLEAR => 'Очистка'
    ];

    /**
     * @var \App\U
     */
    protected static $instance;

    public static function make()
    {
        return static::get();
    }

    /**
     * @return \App\U
     */
    public static function get()
    {
        if (!static::$instance) {
            static::$instance = new \App\U;
        }

        return static::$instance;
    }

    public function __get($k)
    {
        return Auth::user()->$k;
    }

    public static function acl($role, $usingRoot = true)
    {
        if (!Auth::check()) return false;

        return Auth::user()->acl($role, $usingRoot);
    }

    /**
     * @return int
     */
    static public function id()
    {
        return Auth::user()->id;
    }
}