<?php

namespace Spirit\Auth;

interface RecoveryInterface {

    /**
     * @param $user
     * @return static
     */
    public static function user($user);

    public function get();

    public function init();

    public function initForToken($token);
}