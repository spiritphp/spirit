<?php

namespace Spirit\Common\Controllers\Api;

use App\Models\User;
use Spirit\Services\VarCheck;
use Spirit\Request\Client;

abstract class Abc
{

    use ApiTrait;

    const ERROR_LOGIN = 10;
    const ERROR_REGISTRATION = 20;
    const ERROR_REGISTRATION_LOGIN_EXIST = 21;
    const ERROR_TOKEN = 30;
    const ERROR_USER_BLOCK = 403;

    protected $withToken = false;

    /**
     * @var User
     */
    protected $user;
    protected $userToken;
    protected $userId;
    protected $trustTokens = [];

    public static function make()
    {
        return new static();
    }

    public function setTrustTokens($tokens)
    {
        $this->trustTokens = $tokens;
        return $this;
    }

    /**
     * @param $amount
     * @param $time
     * @param bool|false $type
     * @return string|VarCheck
     */
    protected function ipCheck($amount = 15, $time = 2, $type = false)
    {
        return $this->varCheck(Client::getIP(), $amount, $time, $type);
    }

    /**
     * @param $var
     * @param $amount
     * @param $time
     * @param bool|false $type
     * @return string|VarCheck
     */
    protected function varCheck($var, $amount = 15, $time = 2, $type = false)
    {
        $ipCheck = VarCheck::make($var, $type, 7200);

        if (!$ipCheck->checkAmountLimit($amount)) {
            return 'lock';
        }

        if (!$ipCheck->checkTimeDelay($time)) {
            return 'time';
        }

        return $ipCheck;
    }

    /**
     * @param $error_code
     * @param array $response
     * @param bool|false $error_message
     * @return array
     */
    protected function error($error_code, $response = [], $error_message = false)
    {
        $data = [];
        $data['error'] = [
            'error_code' => $error_code
        ];

        if ($error_message) {
            $data['error']['error_msg'] = $error_message;
        }

        $data['response'] = $response;

        return $data;
    }

    protected function success($response = [])
    {
        return ['response' => $response];
    }

    public function __call($name, array $arguments)
    {
        return $this->error(404);
    }

}