<?php

namespace Spirit\Auth;

interface RecoveryInterface {

    /**
     * @param $user
     * @return static
     */
    public static function user($user);

    /**
     * @param $token
     * @param int $lifeminute
     * @return static
     */
    public static function token($token, $lifeminute = 60);

    public function get();

    public function use();
}