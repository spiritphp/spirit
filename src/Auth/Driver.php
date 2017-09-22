<?php

namespace Spirit\Auth;

use Spirit\Common\Models\User;
use Spirit\Engine;

abstract class Driver
{

    /**
     * @var User
     */
    protected $user;

    public static function userModel()
    {
        return Engine::cfg()->userModel;
    }

    public function check()
    {
        return !is_null($this->user);
    }

    abstract public function init();

    public function guest()
    {
        return is_null($this->user);
    }

    public function id()
    {
        $this->user->id;
    }

    public function user()
    {
        $this->user;
    }

    abstract public function loginById($id, $remember = false);

    abstract public function authorize($filter, $remember = false);

    abstract public function register($filter, $autoAuthorize = true, $remember = false);

    abstract public function logout();

}