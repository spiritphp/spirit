<?php

namespace Spirit\Auth\AppDriver\Provider;

use Spirit\Request;
use Spirit\Response\Redirect;
use Spirit\Response\Session;

// TODO
class OAuth
{

    const ERROR_STATE = 'state';
    const ERROR_EMPTY_RESULT = 'empty_result';
    const ERROR_AUTH = 'auth';

    protected $appID;
    protected $linkApp;
    protected $linkToken;
    protected $tokenParams;
    protected $redirectUri;
    protected $secretKey;
    protected $isProtect = false;

    protected $error = false;

    public $result;
    public $access_token;

    /**
     * @return OAuth
     */
    public static function make()
    {
        return new OAuth();
    }

    public function __construct()
    {

    }

    public function linkApp($v)
    {
        $this->linkApp = $v;

        return $this;
    }

    public function redirectUri($v)
    {
        $this->redirectUri = $v;

        return $this;
    }

    public function protect()
    {
        $this->isProtect = true;

        return $this;
    }

    public function appID($v)
    {
        $this->appID = $v;

        return $this;
    }

    public function linkToken($v)
    {
        $this->linkToken = $v;

        return $this;
    }

    public function tokenParams($v)
    {
        $this->tokenParams = $v;

        return $this;
    }

    public function secretKey($v)
    {
        $this->secretKey = $v;

        return $this;
    }

    protected function getData($code)
    {
        if ($this->isProtect) {
            $state = Request::get('state');

            if (hash_equals(Session::get('oauth_state'), $state)) {
                $this->error = static::ERROR_STATE;
                return;
            }
        }

        $tokenParams = strtr(
            $this->tokenParams,
            [
                '{APP_ID}' => $this->appID,
                '{REDIRECT_URI}' => $this->redirectUri,
                '{CODE}' => $code,
                '{SECRET_KEY}' => $this->secretKey,
            ]
        );

        $result = $this->sendCurl($this->linkToken, $tokenParams);

        if (!$result) {
            $this->error = static::ERROR_EMPTY_RESULT;
            return;
        }

        if (isset($result['error'])) {
            $this->error = static::ERROR_AUTH;
            return;
        }

        $this->result = $result;
        $this->access_token = $result['access_token'];
    }

    protected function sendCurl($link, $params)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Accept: application/json']);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);

        $res = curl_exec($curl);
        curl_close($curl);

        if (!$res) {
            return false;
        }

        return json_decode($res, 1);
    }

    public function auth()
    {
        if (Request::get('code')) {
            $this->getData(Request::get('code'));
            return $this;
        }

        $linkApp = strtr(
            $this->linkApp,
            [
                '{APP_ID}' => $this->appID,
                '{REDIRECT_URI}' => $this->redirectUri,
            ]
        );

        if ($this->isProtect) {
            $v = uniqid('oauth_', mt_rand(1, 100000));

            Session::set('oauth_state',$v);
            $linkApp .= '&state=' . $v;
        }

        Redirect::to($linkApp)->send();

        return false;
    }

    public function getError()
    {
        return $this->error;
    }
}