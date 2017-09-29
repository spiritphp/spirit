<?php

namespace Spirit\Auth\DefaultDriver;

use Spirit\Common\Models\User;

class Password {

    public static function init($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function check($password, $passwordHash)
    {
        return password_verify($password, $passwordHash);
    }

    /**
     * @param User $user
     * @param $password
     * @return string
     */
    public static function set($user, $password)
    {
        $version = uniqid();
        $user->password = static::init($password);
        $user->version = $version;
        $user->save();

        return $version;
    }
}