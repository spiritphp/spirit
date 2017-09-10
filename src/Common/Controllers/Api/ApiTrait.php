<?php

namespace Spirit\Common\Controllers\Api;

use App\Models\User;
use Spirit\Func\Hash;
use Spirit\Request;

trait ApiTrait
{

    /**
     * @var User
     */
    protected $user;

    /**
     * @param string $uid
     * @return User|null
     */
    protected function getUserForUid($uid)
    {
        return User::where('uid', $uid)->first();
    }

    /**
     * @param integer $id
     * @return User|null
     */
    protected function getUserForId($id)
    {
        return User::find($id);
    }

    /**
     * @param string $token
     * @return User|null
     */
    protected function getUserForToken($token)
    {
        return User::where('token', $token)->first();
    }

    protected function initUserForToken()
    {
        if (!$token = Request::get('token')) {
            return false;
        }

        if (!$user = $this->getUserForToken($token)) {
            return false;
        }

        $this->user = $user;

        return true;
    }

    protected function getUser(User $user, $ext = null)
    {
        $user->date_online = 'NOW()';
        $user->save();

        if ($user->login) {
            $nick = $user->login;
        } else {
            $nickArr = [];

            if ($user->info->first_name) {
                $nickArr[] = $user->info->first_name;
            }

            if ($user->info->last_name) {
                $nickArr[] = $user->info->last_name;
            }

            $nick = trim(implode(' ', $nickArr));
        }

        $return = [
            'crc' => Hash::h($user->id),
            'nick' => $nick,
            'token' => $user->token,
            'uid' => $user->uid
        ];

        if ($ext) {
            if (!is_array($ext)) {
                $ext = [$ext];
            }

            foreach ($ext as $key) {
                $return[$key] = $user->$key;
            }
        }

        return $return;
    }

    public function isTrust()
    {
        if (!isset($this->trustTokens)) return false;

        if (!$trust_token = Request::get('trust_token')) {
            return false;
        }

        return in_array($trust_token, $this->trustTokens);
    }

    public function checkToken()
    {
        if (isset($this->withToken) && $this->withToken) {
            if (!$this->initUserForToken()) {
                return false;
            }
        }

        return true;
    }
}