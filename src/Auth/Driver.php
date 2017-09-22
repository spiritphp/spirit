<?php

namespace Spirit\Auth;

use Spirit\Common\Models\User;
use Spirit\Engine;

abstract class Driver
{
    /**
     * @var Storage
     */
    protected $storage;

    public function __construct()
    {
        $this->storage = new Storage();
    }

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

    /**
     * @return User
     */
    public function user()
    {
        return $this->user;
    }

    /**
     * @return Storage
     */
    public function storage()
    {
        return $this->storage;
    }

    abstract public function loginById($id, $remember = false);

    abstract public function authorize($filter, $remember = false);

    abstract public function register($filter, $autoAuthorize = true, $remember = false);

    abstract public function logout();

    abstract public function setPassword($password);

    abstract public function recovery();

}