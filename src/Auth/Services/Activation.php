<?php

namespace Spirit\Auth\Services;

use Spirit\Auth\Hash;
use Spirit\Common\Models\User as CommonUser;
use App\Models\User;
use Spirit\Engine;
use Spirit\Services\Mail;
use Spirit\Request\URL;
use Spirit\View;

class Activation
{

    protected $code;
    protected $hash;
    protected $userID;
    protected $userUID;
    protected $email;
    protected $login;

    public static function make()
    {
        return new Activation();
    }

    public function __construct()
    {

    }

    public function setCode($code)
    {
        $this->code = $code;

        $hash_arr = explode('::', base64_decode($code), 2);

        $this->userUID = $hash_arr[0];
        $this->hash = $hash_arr[1];

        return $this;
    }

    /**
     * @return User|CommonUser|null
     */
    public function activate()
    {
        // Проверка
        if (!password_verify($this->userUID, $this->hash)) return null;

        $user = User::where('uid',$this->userID)->first();

        if (!$user) return null;

        if ($user->active) return null;

        $user->active = true;
        $user->save();

        return $user;
    }

    public function setUserID($v)
    {
        $this->userID = $v;

        return $this;
    }

    public function setUID($v)
    {
        $this->userUID = $v;

        return $this;
    }

    public function setEmail($v)
    {
        $this->email = $v;

        return $this;
    }

    public function setLogin($v)
    {
        $this->login = $v;

        return $this;
    }

    public function send($title, $view)
    {
        // TODO переделать линк без юзер ID
        Mail::send(
            $view,
            [
                'user_id' => $this->userID,
                'email' => $this->email,
                'login' => $this->login,
                'link_activation' => Engine::i()->url . 'activation/' .
                    base64_encode($this->userUID . '::' . Hash::activation($this->userUID)),
                'url' => Engine::i()->url
            ],
            function (Mail\Message $message) use ($title) {
                $message->to($this->email)
                    ->subject($title);
            }
        );
    }
}