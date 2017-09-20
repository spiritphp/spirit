<?php

namespace Spirit\Auth\Services;

use App\Models\User;
use Spirit\Auth;
use Spirit\DB;

class Password extends Service
{
    protected $userID;
    protected $password;
    protected $versionCurrent;
    protected $versionNew;

    public function setUserID($v)
    {
        $this->userID = $v;

        return $this;
    }

    public function setCurrentVersion($v)
    {
        $this->versionCurrent = $v;

        return $this;
    }

    public function setNewVersion($v)
    {
        $this->versionNew = $v;

        return $this;
    }

    public function update($password)
    {
        if (!$this->userID) return false;

        if (!$this->versionNew) $this->versionNew = mt_rand(0, 999999999);

        $userClass = static::userModel();

        /**
         * @var \Spirit\Common\Models\User
         */
        $user = $userClass::find($this->userID);

        if (!$user) return false;

        if (!$this->versionCurrent) {
            $this->versionCurrent = $user->version;
        }

        $user->password = Auth\Hash::password($password);
        $user->version = DB::raw($this->versionNew . '::varchar');
        $user->save();

        if ($user->save()) {
            Auth::setUserCookie($this->userID, $user->version);
        }

        return true;

    }

}