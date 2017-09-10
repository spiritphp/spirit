<?php

namespace Spirit\Common\Controllers;

use App\Models\User;
use Spirit\Auth;
use Spirit\Request;
use Spirit\Structure\Controller;
use Spirit\Common\Controllers\Api as commonApi;

class ApiController extends Controller
{

    use commonApi\ApiTrait;

    protected $trustTokens = [];

    protected $allowedParent = [
        'user' => 'User'
    ];

    protected function makeJson($arr)
    {
        return $this->json($arr)->headers([
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'POST, GET, DELETE, PUT',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Headers' => 'Content-Type, X-Requested-With'
        ]);
    }

    protected function error($error_code, $response = false, $error_message = false)
    {
        $err = [
            'error' => [],
        ];

        $err['error']['error_code'] = $error_code;

        if ($error_message) {
            $err['error']['error_msg'] = $error_message;
        }

        if ($response) {
            $err['response'] = $response;
        }

        return $this->makeJson($err);
    }

    /**
     * @param $methodName
     * @return commonApi\Abc|null
     */
    protected function getApiClass($methodName)
    {
        if (!isset($this->allowedParent[$methodName])) {
            return null;
        }

        /**
         * @var commonApi\Abc $className
         */
        $className = $this->allowedParent[$methodName];

        if (strpos($className, '\Spirit\Common\\') !== 0) {
            $className = 'App\Controllers\\Api\\' . $className;
        }

        $class = $className::make();
        $class->setTrustTokens($this->trustTokens);

        return $class;
    }

    public function callApi($method)
    {
        if (!$method) {
            return $this->error(404);
        }

        $methodArr = explode('.', $method, 2);

        if (count($methodArr) == 1) {
            $methodName = $methodArr[0];

            if (!method_exists($this, $methodName)) {
                return $this->error(404);
            }

            $result = call_user_func([$this, $methodName]);

        } else {

            /**
             * @var commonApi\Abc $class
             */
            if (!$class = $this->getApiClass($methodArr[0])) {
                return $this->error(404);
            }

            if (!$class->checkToken()) {
                return $this->error(commonApi\Abc::ERROR_TOKEN);
            }

            $result = call_user_func([$class, $methodArr[1]]);
        }

        if (!$result) {
            return $this->error(500);
        }

        if (!isset($result['response'])) {
            $r = [];
            $r['response'] = $result;
            $result = $r;
        }

        return $this->makeJson($result);
    }

    public function ping()
    {
        return ['pong'];
    }

    public function pingTwo()
    {
        $ping = Request::get('ping');

        return [
            'pong' => $ping,
        ];
    }

    public function pingTrust()
    {
        if (!$this->isTrust()) {
            return null;
        }

        return ['trust'];
    }
}