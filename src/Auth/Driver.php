<?php

namespace Spirit\Auth;

abstract class Driver {

    abstract public function check();

    abstract public function init();

    abstract public function guest();

    abstract public function id();

    abstract public function user();

    abstract public function setUserCookie($user_id, $version = null);

    abstract public function loginById();

    abstract public function logout();

}