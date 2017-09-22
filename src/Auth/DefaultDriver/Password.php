<?php

namespace Spirit\Auth\DefaultDriver;

class Password {

    public static function init($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function check($password, $passwordHash)
    {
        return password_verify($password, $passwordHash);
    }

}