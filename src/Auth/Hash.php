<?php

namespace Spirit\Auth;

class Hash extends \Spirit\Func\Hash
{

    public static function app($alias, $id)
    {
        return static::h([$alias, $id]);
    }

    public static function password($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function activation($user_id)
    {
        return password_hash($user_id, PASSWORD_DEFAULT);
    }

    public static function recovery($user_id)
    {
        return static::h([$user_id, uniqid(mt_rand(0, 50000))]);
    }
}