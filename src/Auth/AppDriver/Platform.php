<?php

namespace Spirit\Auth\AppDriver;

use Spirit\Auth\AppDriver\Provider\OAuth;
use Spirit\Auth\Hash;
use Spirit\Auth\Services\Login;
use Spirit\Auth\Services\Registration;
use Spirit\FileSystem;
use Spirit\Response\Session;
use Spirit\DB;
use Spirit\Services\Send;
use Spirit\Request\URL;

abstract class Platform
{

    protected $alias;

    protected $error;

    protected $appUserID;
    protected $appToken;
    protected $appData;
    protected $appUserData;

    protected $cfg;
    protected $session;

    function __construct($cfg = [])
    {
        $this->cfg = $cfg;
        $this->session = Session::storage('app');
    }

    public function getError()
    {
        return $this->error;
    }

    protected function log($m)
    {
        if (is_array($m)) {
            $m = json_encode($m, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
        $log_dir = 'log_' . $this->alias;

        FileSystem\Log::w($m, $log_dir);
    }

    protected function sendGet($url, $params = [])
    {
        if (!$result = Send::get($url, $params)) {
            return null;
        }

        try {
            return json_decode($result, 1);
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function sendPost($url, $params = [])
    {
        if (!$result = Send::post($url, $params)) {
            return null;
        }

        try {
            return json_decode($result, 1);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @return OAuth
     */
    protected function oauth()
    {
        return OAuth::make()
            ->appID($this->cfg['app_id'])
            ->redirectUri(URL::path())
            ->linkApp($this->cfg['link_app'])
            ->protect()
            ->linkToken($this->cfg['link_token'])
            ->tokenParams($this->cfg['token_params'])
            ->secretKey($this->cfg['secret_key'])
            ->auth();
    }

    /**
     * @return \App\Models\User|null
     */
    protected function user()
    {
        $this->appUserData['all'] = $this->appData;
        $this->appUserData['token'] = $this->appToken;
        $this->appUserData['id'] = $this->appUserID;
        $this->appUserData['alias'] = $this->alias;

        $appUser = new User($this->appUserData);

        if (!$user = Login::make()->setApp($appUser->alias, $appUser->id, $appUser->token)->getUser()) {
            // Регистрируемся
            $user = Registration::make()
                ->setApp($appUser->alias, $appUser->id, $appUser->token)
                ->setField([
                    'first_name' => $appUser->first_name,
                    'last_name' => $appUser->last_name,
                    'gender' => $appUser->gender,
                    'birthday' => $appUser->birthday,
                ])
                ->create();;
        }

        return $user;
    }

    public function getConfig($key)
    {
        if (isset($this->cfg[$key])) return $this->cfg[$key];

        return null;
    }

    public function searchUserId($app_user_id = false)
    {
        $hash = Hash::app($this->alias, $app_user_id);

        $appUser = DB::table('user_app')
            ->where('hash', $hash)
            ->first();

        if (!$appUser) return false;

        return $appUser['user_id'];
    }

    public function callbackPayment()
    {
        return false;
    }

    /**
     * Для приложений в соцсетях (iframe,mobile)
     *
     * @return \App\Models\User|null
     */
    public function appUser()
    {
        if (!$get = $this->getParams()) {
            return null;
        }

        if (!$this->initAppUser($get)) {
            return null;
        }

        return $this->user();
    }

    /**
     * Для авторизации через соцсети
     * @return \App\Models\User|null
     */
    public function providerUser()
    {
        if (!$this->initProviderUser()) return null;

        return $this->user();
    }

    protected abstract function initProviderUser();

    /**
     * Параметры из строки запроса
     * @return mixed
     */
    protected abstract function getParams();

    protected abstract function initAppUser($get);

    /**
     * Выход для приложений
     * @return mixed
     */
    public abstract function logout();

    /**
     * Редирект на приложение
     * @return mixed
     */
    public abstract function redirectInApp();
}