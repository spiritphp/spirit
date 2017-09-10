<?php

namespace Spirit\Auth\AppDriver;

use Spirit\Request;
use Spirit\Response\Redirect;
use Spirit\Auth;

class Ok extends Platform
{

    protected $alias = 'ok';

    function apiCall($method, $params = array(), $api_server = 'http://api.odnoklassniki.ru/', $return = false)
    {
        $params['application_key'] = $this->cfg['application_key'];
        $params['method'] = $method;

        ksort($params);
        $p = '';
        foreach($params as $key => $value) {
            $p .= "$key=$value";
        }

        $sig = md5($p . $this->cfg['secret_key']);
        $params['sig'] = $sig;

        if ($return) {
            return $api_server . 'fb.do?' . http_build_query($params);
        } else {
            return $this->sendGet($api_server . 'fb.do', $params);
        }

    }

    /**
     *
     * Инициализация пользователя
     *
     */
    protected function getParams()
    {
        if (!Request::get('sig') || !Request::get('session_secret_key')) return false;

        $params = array(
            'logged_user_id',
            'api_server',
            'application_key',
            'session_key',
            'session_secret_key',
            'lang',
            'sig',
            'container'
        );

        $g = Request::get()->only($params);

        $sig = $_GET['sig'];
        unset($_GET['sig']);

        ksort($_GET);
        $params = "";
        foreach($_GET as $key => $value) {
            $params = $params . $key . '=' . $value;
        }

        $sig2 = md5($params . $this->cfg['secret_key']);

        // проверка подписей
        if (strtolower($sig) !== strtolower($sig2)) {
            $this->log(implode("\n", $_GET) . "\n" . $sig . '!=' . $sig2);
            return false;
        }

        return $g;
    }

    protected function getAppUserData($res)
    {
        $data = [];

        foreach($this->cfg['get_fields'] as $key => $value) {
            if (!isset($res[$key]) || !$res[$key]) continue;

            if ($value == 'birthday') {
                if ($res[$key] && preg_match("/^\d{4}\-\d{1,2}\-\d{1,2}$/i", $res[$key])) {
                    list($year, $month, $day) = explode('-', $res[$key]);

                    if (!checkdate($month, $day, $year)) continue;

                    $res[$key] = $year . '-' . (strlen($month) == 1 ? '0' . $month : $month) . '-' . (strlen($day) == 1 ? '0' . $day : $day);

                } else {
                    continue;
                }
            }

            if ($value == 'gender') {
                $res[$key] = strtr($res[$key], array('male' => 2, 'female' => 1));

                if (!is_numeric($res[$key])) $res[$key] = 0;

            }

            $data[$value] = $res[$key];
        }

        return $data;
    }

    protected function initAppUser($get)
    {
        // Делаем запрос
        $params = array(
            'fields' => 'uid,first_name,last_name,gender,birthday',
            'uids' => $get['logged_user_id'],
            'session_secret_key' => $get['session_secret_key'],
        );

        $res = $this->apiCall('users.getInfo', $params, $get['api_server']);

        if (!isset($res['0']) || !isset($res['0']['uid'])) {
            $this->log("NOT FOUND UID:\n" . implode("\n", $get));
            return false;
        }

        $res = $res['0'];

        $get['uid'] = $res['uid'];

        $this->appUserID = $get['uid'];
        $this->appToken = $get['session_secret_key'];
        $this->appData = $res;
        $this->appUserData = $this->getAppUserData($res);

        return true;
    }

    public function initProviderUser()
    {
        $oAuth = $this->oauth();

        $sign = md5(
            'application_key=' . $this->cfg['public_key'] .
            'method=users.getCurrentUser' .
            md5($oAuth->access_token . $this->cfg['secret_key'])
        );

        $params = [
            'access_token' => $oAuth->access_token,
            'application_key' => $this->cfg['public_key'],
            'method' => 'users.getCurrentUser',
            'sig' => $sign
        ];

        $res = $this->sendGet('http://api.odnoklassniki.ru/fb.do', $params);

        if (!isset($res['uid'])) {
            $this->error = 'error_uid';
            return null;
        }

        $this->appUserID = $res['uid'];
        $this->appToken = $oAuth->access_token;
        $this->appData = $res;
        $this->appUserData = $this->getAppUserData($res);

        return true;
    }

    protected function calcSignature($request)
    {
        $tmp = $request;
        unset($tmp["sig"]);
        ksort($tmp);
        $resstr = "";
        foreach($tmp as $key => $value) {
            $resstr = $resstr . $key . "=" . $value;
        }
        $resstr = $resstr . $this->cfg['secret_key'];

        return md5($resstr);

    }

    public function callbackPayment()
    {
        if (!Request::has(['sig', 'uid', 'product_code'])) return false;

        $params = [
            'uid',
            'transaction_time',
            'transaction_id',
            'product_code',
            'product_option',
            'amount',
            'currency',
            'payment_system',
            'extra_attributes',
            'sig',
        ];

        $g = Request::get()->only($params);
        $sig = $_GET['sig'];
        $sig2 = $this->calcSignature($_GET);

        // проверка подписей
        if (strtolower($sig) !== strtolower($sig2)) return false;

        return $g;
    }

    function getPayLink($service_id, $ok_price, $service_name)
    {
        $p = [];
        $p['code'] = $service_id;
        $p['name'] = $service_name;
        $p['price'] = $ok_price;
        $p['application_key'] = $this->cfg['application_key'];
        $p['session_key'] = $this->session['params']['session_key'];

        ksort($p);

        $params = '';
        foreach($p as $key => $value) {
            $params .= $key . "=" . $value;
        }

        $sig = md5($params . $this->session['session_secret_key']);

        $p['sig'] = $sig;

        Redirect::to('http://m.odnoklassniki.ru/api/show_payment?' . http_build_query($p));

    }

    public function logout()
    {
        Auth::logout();
        Redirect::to('http://m.ok.ru/app/' . $this->cfg['app_id'])->send();
    }

    public function redirectInApp()
    {
        Redirect::to('http://m.ok.ru/app/' . $this->cfg['app_id'])->send();
    }
}