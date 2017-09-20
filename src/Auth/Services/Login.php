<?php

namespace Spirit\Auth\Services;

use Spirit\Common\Models\User as CommonUser;

class Login extends Service
{

    protected $email;
    protected $password;
    protected $appHash;

    public function setEmail($v)
    {
        $this->email = $v;

        return $this;
    }

    public function setPassword($v)
    {
        $this->password = $v;

        return $this;
    }

    /**
     * @return CommonUser|null
     */
    public function getUser()
    {
        $className = static::userModel();
        $user = $className::where('email', $this->email)->first();

        if (!$user) {
            return null;
        }

        if (!password_verify($this->password, $user->password)) return null;

        return $user;
    }
}