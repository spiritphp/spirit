<?php

namespace Spirit\Auth\Services;

use App\Models\User;
use Spirit\Auth\Hash;
use Spirit\Request\Client;
use Spirit\DB;
use Spirit\Request\URL;
use Spirit\Services\Mail;
use Spirit\View;

class Recovery
{

    protected $login;
    protected $email;
    protected $hash;
    protected $recoveryData;
    protected $isCheck = false;

    public function __construct()
    {

    }

    public static function make()
    {
        return new Recovery();
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

    public function setHash($v)
    {
        $this->hash = $v;

        return $this;
    }

    public function send($title, $view)
    {
        /**
         * @var User $user
         */
        $user = false;
        if ($this->login) {
            $user = User::where('login', $this->login)
                ->first();
        }

        if (!$user && $this->email) {
            $user = User::where('email', $this->email)
                ->first();
        }

        if (!$user)
            return false;

        if (!$user->email)
            return false;

        $hash = Hash::recovery($user->id);
        $ip = Client::getIP();

        DB::table('user_recovery')
            ->insert([
                'user_id' => $user['id'],
                'ip' => Client::getIP(),
                'hash' => $hash
            ]);

        Mail::createMessage($view, [
            'ip' => $ip,
            'user_id' => $user['id'],
            'email' => $user['email'],
            'login' => $user['login'],
            'link_recovery' => URL::make('recovery/' . $hash),
            'url' => URL::current()
        ])
            ->to($user['email'])
            ->subject($title)
            ->send();

        return true;
    }


    public function check()
    {
        $recovery = DB::table('user_recovery')
            ->where('hash', $this->hash)
            ->first();

        $this->isCheck = false;
        $this->recoveryData = false;

        if (!$recovery)
            return false;

        if (!$recovery['active'])
            return false;

        $this->isCheck = true;
        $this->recoveryData = $recovery;

        return true;
    }

    public function setNewPassword($password)
    {
        if (!$this->isCheck || !$this->recoveryData)
            return false;

        DB::beginTransaction();

        $result = Password::make()
            ->setUserID($this->recoveryData['user_id'])
            ->update($password);

        if (!$result) {
            DB::rollback();

            return false;
        }

        $result = $this->useRecovery();

        if ($result == 0) {
            DB::rollback();

            return false;
        }

        DB::commit();

        return true;
    }

    public function useRecovery()
    {
        return DB::table('user_recovery')
            ->where('id', $this->recoveryData['id'])
            ->where('active', true)
            ->update([
                'active' => false,
                'ip_use' => Client::getIP(),
                'used_at' => DB::raw('NOW()')
            ]);
    }

    public function getUserID()
    {
        if (!$this->isCheck || !$this->recoveryData)
            return false;

        return $this->recoveryData['user_id'];
    }

    /**
     * @return User|null
     */
    public function getUser()
    {
        if (!$this->isCheck || !$this->recoveryData)
            return null;

        $user = User::find($this->recoveryData['user_id']);

        return $user;
    }


}