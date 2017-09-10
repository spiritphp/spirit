<?php

namespace Spirit\Auth\Services;

use App\Models\User;
use Spirit\Auth\Hash;
use Spirit\Common\Models\User as commonUser;
use Spirit\Request\URL;
use Spirit\Services\Mail;
use Spirit\Request\Client;
use Spirit\DB;
use Spirit\Func;
use Spirit\View;

class Registration
{

    protected $login;
    protected $email;
    protected $password;
    protected $needActivation = false;
    protected $isTemp = false;

    protected $fieldData;

    protected $appAlias;
    protected $appUserID;
    protected $appToken;

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

    public function setApp($alias, $id, $token)
    {
        $this->appAlias = $alias;
        $this->appUserID = $id;
        $this->appToken = $token;

        return $this;
    }

    public function setField($data)
    {
        $this->fieldData = $data;

        return $this;
    }

    public function repeatActivationForUserID()
    {

    }

    /**
     * @return User|\Spirit\Common\Models\User
     */
    public function create()
    {
        DB::beginTransaction();

        /**
         * @var User|\Spirit\Common\Models\User $user
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
        $user->ip = Client::getIP();
        $user->active = $this->needActivation ? false : true;
        $user->token = hash('sha256', uniqid(mt_rand(0, 10000000)));
        $user->save();

        $user_id = $user->id;

        if ($this->appUserID && $this->appAlias && $this->appToken) {

            $app = new commonUser\App();
            $app->app_user_id = $this->appUserID;
            $app->alias = $this->appAlias;
            $app->token = $this->appToken;
            $app->hash = Hash::app($this->appAlias, $this->appUserID);
            $user->apps()
                ->save($app);
        }

        $info = new commonUser\Info();
        if ($this->fieldData) {
            $info->info = $this->fieldData;
        }
        $user->info()
            ->save($info);

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

            if ($this->messageActivationView) {

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