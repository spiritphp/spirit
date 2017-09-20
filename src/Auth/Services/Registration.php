<?php

namespace Spirit\Auth\Services;

use Spirit\Auth\Hash;
use Spirit\Common\Models\User as CommonUser;
use Spirit\Func;

class Registration extends Service
{
    protected $email;
    protected $password;

    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    public function create()
    {
        $userClass = static::userModel();

        /**
         * @var CommonUser $user
         */
        $user = new $userClass();

        $user->email = $this->email ? $this->email : null;

        if ($this->password) {
            $user->password = Hash::password($this->password);
        }

        $user->uid = Func\Func::unique_id(10);
        $user->token = hash('sha256', uniqid(mt_rand(0, 10000000)));
        $user->save();

        return $user;
    }

}