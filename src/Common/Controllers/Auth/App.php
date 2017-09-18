<?php

namespace Spirit\Common\Controllers\Auth;

use Spirit\Auth\AppDriver\Platform;
use Spirit\Common\Models\User;
use Spirit\Request\URL;

class App
{

    protected $cfg = [
        'vk' => [
            'class' => 'Vk',
            'site_id' => 1,
            'name' => 'VK',
            'app_id' => '{APP_ID}',
            'secret_key' => '{SECRET_KEY}',
            'link_app' => 'https://oauth.vk.com/authorize?client_id={APP_ID}&redirect_uri={REDIRECT_URI}&display=mobile',
            'link_token' => 'https://oauth.vk.com/access_token',
            'token_params' => 'client_id={APP_ID}&client_secret={SECRET_KEY}&code={CODE}&redirect_uri={REDIRECT_URI}',
            'state' => true,
            'get_fields' => [
                'uid' => 'id',
                'first_name' => 'first_name',
                'last_name' => 'last_name',
                'sex' => 'gender',
                'bdate' => 'birthday',
                'photo_100' => 'picture',
                'nickname' => 'nick',
            ],
        ],
        'mailru' => [
            'class' => 'Mailru',
            'site_id' => 4,
            'name' => 'Mail.Ru',
            'app_id' => '{APP_ID}',
            'secret_key' => '{SECRET_KEY}',
            'private_key' => '{PRIVATE_KEY}',
            'link_app' => 'https://connect.mail.ru/oauth/authorize?response_type=code&client_id={APP_ID}&redirect_uri={REDIRECT_URI}',
            'link_token' => 'https://connect.mail.ru/oauth/token',
            'token_params' => 'code={CODE}&client_id={APP_ID}&client_secret={SECRET_KEY}&redirect_uri={REDIRECT_URI}&grant_type=authorization_code',
            'state' => true,
            'get_fields' => [
                'uid' => 'id',
                'email' => 'email',
                'first_name' => 'first_name',
                'last_name' => 'last_name',
                'sex' => 'gender',
                'birthday' => 'birthday',
                'pic_big' => 'picture',
            ],
        ],
        'ok' => [
            'class' => 'Ok',
            'site_id' => 7,
            'name' => 'Одноклассники',
            'app_id' => '{APP_ID}',
            'secret_key' => '{SECRET_KEY}',
            'public_key' => '{PUBLIC_KEY}',
            'link_app' => 'http://www.odnoklassniki.ru/oauth/authorize?response_type=code&client_id={APP_ID}&redirect_uri={REDIRECT_URI}&layout=m',
            'link_token' => 'http://api.odnoklassniki.ru/oauth/token.do',
            'token_params' => 'code={CODE}&client_id={APP_ID}&client_secret={SECRET_KEY}&redirect_uri={REDIRECT_URI}&grant_type=authorization_code',
            'state' => false,
            'get_fields' => [
                'uid' => 'id',
                'first_name' => 'first_name',
                'last_name' => 'last_name',
                'gender' => 'gender',
                'birthday' => 'birthday',
                'pic_2' => 'picture',
            ],
        ],
    ];

    protected $error = false;

    public static function make($cfg)
    {
        return new App($cfg);
    }

    public function getError()
    {
        return $this->error;
    }

    public function __construct($cfg)
    {
        $this->cfg = $cfg;
    }

    public function getAppsInfo()
    {
        $list = [];

        foreach ($this->cfg as $key => $item) {
            $list[] = [
                'name' => $item['name'],
                'alias' => $key,
                'link' => URL::make('auth/' . $key)
            ];
        }

        return $list;
    }

    /**
     * @param $appName
     * @return null|User
     */
    public function auth($appName)
    {
        if (!isset($this->cfg[$appName])) return null;

        $config = $this->cfg[$appName];

        $className = $config['class'];

        if (strpos($className, '\\') === false) {
            $className = 'Spirit\Auth\App\\' . $className;
        }

        /**
         * @var Platform $class
         */
        $class = new $className($config);

        $user = $class->providerUser();

        if (!$user) {
            $this->error = $class->getError();
            return null;
        }

        return $user;
    }
}