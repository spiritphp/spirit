<?php

namespace Spirit\Auth\Services;

use Spirit\Auth\Hash;
use Spirit\Common\Models\User as CommonUser;
use App\Models\User;
use Spirit\DB;

class Login
{

    protected $login;
    protected $email;
    protected $password;
    protected $appHash;

    public static function make()
    {
        return new Login();
    }

    public function __construct()
    {

    }

    public function setLogin($v)
    {
        $this->login = $v;

        if (!$this->email && filter_var($v, FILTER_VALIDATE_EMAIL)) {
            $this->email = $v;
        }

        return $this;
    }

    public function setEmail($v)
    {
        $this->email = $v;

        if (!$this->login) {
            $this->login = $this->email;
        }

        return $this;
    }

    public function setPassword($v)
    {
        $this->password = $v;

        return $this;
    }

    /**
     * @return User|CommonUser|null
     */
    public function getUser()
    {
        /**
         * @var User|CommonUser $user
         */

        $user = false;
        if ($this->login) {
            $user = User::where('login', $this->login)->first();
        }

        if (!$user && $this->email) {
            $user = User::where('email', $this->email)->first();
        }

        if ($user) {
            if (!password_verify($this->password, $user->password)) return null;
        }

        return $user;
    }
}