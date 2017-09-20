<?php

namespace Spirit\Auth\Services;

use App\Models\User;
use Spirit\Auth\Hash;
use Spirit\Common\Models\User as CommonUser;
use Spirit\Request\URL;
use Spirit\Services\Mail;
use Spirit\DB;
use Spirit\Func;

class Registration
{

    protected $login;
    protected $email;
    protected $password;
    protected $needActivation = false;
    protected $isTemp = false;

    protected $messageActivationTitle;
    protected $messageActivationView;

    protected $messageWelcomeView;
    protected $messageWelcomeTitle;

    public function __construct()
    {

    }

    public static function make()
    {
        return new Registration();
    }

    public function setLogin($login)
    {
        $this->login = $login;

        return $this;
    }

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

    public function withActivation()
    {
        $this->needActivation = true;

        return $this;
    }

    public function sendActivation($title = false, $messageView = false)
    {
        $this->messageActivationView = $messageView;
        $this->messageActivationTitle = $title;

        return $this;
    }

    public function sendWelcome($title = false, $messageView = false)
    {
        $this->messageWelcomeView = $messageView;
        $this->messageWelcomeTitle = $title;

        return $this;
    }

    public function repeatActivationForUserID()
    {

    }

    /**
     * @return User|CommonUser
     */
    public function create()
    {
        DB::beginTransaction();

        /**
         * @var User|CommonUser $user
         */
        $user = new User();

        if ($this->login || $this->email) {
            $user->login = $this->login ? $this->login : $this->email;
            $user->email = $this->email ? $this->email : null;
        }

        if ($this->password) {
            $user->password = Hash::password($this->password);
        }

        $user->uid = Func\Func::unique_id(8);
        $user->token = hash('sha256', uniqid(mt_rand(0, 10000000)));
        $user->save();

        $user_id = $user->id;

        DB::commit();

        if ($this->email) {
            if ($this->messageWelcomeView) {

                Mail::createMessage($this->messageWelcomeView, [
                    'user_id' => $user_id,
                    'uid' => $user->uid,
                    'email' => $this->email,
                    'login' => $this->login,
                    'url' => URL::current()
                ])
                    ->to($this->email)
                    ->subject($this->messageWelcomeTitle)
                    ->send();
            }

            if ($this->needActivation && $this->messageActivationView) {

                Activation::make()
                    ->setUserID($user_id)
                    ->setUID($user->uid)
                    ->setEmail($this->email)
                    ->setLogin($this->login)
                    ->send($this->messageWelcomeTitle, $this->messageActivationView);
            }
        }

        return $user;
    }

}