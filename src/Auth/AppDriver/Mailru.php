<?php

namespace Spirit\Auth\AppDriver;

use Spirit\Request;
use Spirit\Response\Redirect;
use Spirit\Auth;

class Mailru extends Platform
{

    protected $alias = 'mailru';

    function apiCall($method, $params = [])
    {
        $params['app_id'] = $this->cfg['app_id'];
        $params['secure'] = 1;
        $params['method'] = $method;

        ksort($params);
        $p = '';
        foreach ($params as $key => $value) {
            $p .= "$key=$value";
        }

        $sig = md5($p . $this->cfg['secret_key']);
        $params['sig'] = $sig;

        return $this->sendGet('http://www.appsmail.ru/platform/api', $params);
    }

    /**
     *
     * Инициализация пользователя
     *
     */
    protected function getParams()
    {
        if (!isset($_GET['sig']) || !isset($_GET['session_key'])) return false;

        $params = array(
            'app_id',
            'session_key',
            'session_expire',
            'oid',
            'vid',
            'is_app_user',
            'ext_perm',
            'authentication_key',
            'window_id',
            'view',
            'sig',
            'mobile_spec',
            'refer_id',
            'refer_type',
        );

        $g = Request::get()->only($params);

        $sig = $_GET['sig'];
        unset($_GET['sig']);

        ksort($_GET);
        $params = "";
        foreach ($_GET as $key => $value) {
            $params = $params . $key . "=" . $value;
        }

        $sig2 = md5($params . $this->cfg['secret_key']);

        // проверка подписей
        if (strtolower($sig) !== strtolower($sig2)) {
            //die('Технические работы. Всё заработает через 15 минут.');
            $this->log(implode("\n", $_GET) . "\n" . $sig . '!=' . $sig2);
            return false;
        }

        return $g;
    }

    protected function getAppUserData($res)
    {
        $data = [];

        foreach ($this->cfg['get_fields'] as $key => $value) {
            if (!isset($res[$key]) || !$res[$key]) continue;

            if ($value == 'birthday') {
                if ($res[$key] && preg_match("/^\d{1,2}\.\d{1,2}\.\d{4}$/i", $res[$key])) {
                    list($day, $month, $year) = explode('.', $res[$key]);

                    if (!checkdate($month, $day, $year)) continue;

                    $res[$key] = $year . '-' . (strlen($month) == 1 ? '0' . $month : $month) . '-' . (strlen($day) == 1 ? '0' . $day : $day);

                } else {
                    continue;
                }
            }

            $data[$value] = $res[$key];
        }

        return $data;
    }

    protected function initAppUser($get)
    {
        // Делаем запрос
        $params = array(
            'session_key' => $get['session_key'],
            'uids' => $get['vid'],
            //'fields' => implode(',',array_keys($this->cfg['get_fields']))
        );

        $res = $this->apiCall('users.getInfo', $params);

        if (!isset($res['0']) || !isset($res['0']['uid'])) {
            $this->log("NOT FOUND UID:\n" . implode("\n", $get));
            return false;
        }

        $res = $res['0'];

        $this->appUserID = $res['uid'];
        $this->appToken = $get['session_key'];
        $this->appData = $res;
        $this->appUserData = $this->getAppUserData($res);

        return true;
    }

    public function initProviderUser()
    {
        $oAuth = $this->oauth();

        $params = [];

        $params['method'] = 'users.getInfo';
        $params['api_id'] = $this->cfg['app_id'];
        $params['uids'] = $oAuth->result['x_mailru_vid'];
        $params['uid'] = $oAuth->result['x_mailru_vid'];
        $params['secure'] = 1;

        $params['sig'] = md5(
            'api_id=' . $params['api_id'] .
            'method=' . $params['method'] .
            'secure=' . $params['secure'] .
            'uid=' . $params['uid'] .
            'uids=' . $params['uids'] .
            $this->cfg['secret_key']
        );

        $res = $this->sendGet('http://www.appsmail.ru/platform/api', $params);
        $res = $res[0];

        if (!isset($res['uid'])) {
            $this->error = 'error_id';
            return null;
        }

        $this->appUserID = $res['uid'];
        $this->appToken = $oAuth->access_token;
        $this->appData = $res;
        $this->appUserData = $this->getAppUserData($res);

        return true;
    }

    function getPayLink($service_id, $mailiki_price, $service_name)
    {
        $p = array();

        $p['appid'] = $this->cfg['app_id'];
        $p['service_id'] = $service_id;
        $p['mailiki_price'] = $mailiki_price;
        $p['service_name'] = $service_name;
        $p['mob'] = 1;

        Redirect::to('http://m.my.mail.ru/cgi-bin/app/paymentm?' . http_build_query($p))->send();
    }

    protected function calcSignature($request)
    {
        $tmp = $request;
        unset($tmp["sig"]);
        ksort($tmp);
        $resstr = "";
        foreach ($tmp as $key => $value) {
            $resstr = $resstr . $key . "=" . $value;
        }
        $resstr = $resstr . $this->cfg['secret_key'];

        return md5($resstr);

    }

    public function callbackPayment()
    {
        if (!isset($_GET['sig']) || !isset($_GET['uid'])) {
            return false;
        }

        $params = array(
            'app_id',
            'transaction_id',
            'service_id',
            'uid',
            'sig',
            'mailiki_price',
            'other_price',
            'profit',
            'debug'
        );

        if (isset($_GET['sms_price'])) {
            $params[] = 'sms_price';
        }

        $g = Request::get()->only($params);

        $sig = $_GET['sig'];
        $sig2 = $this->calcSignature($_GET);

        // проверка подписей
        if (strtolower($sig) !== strtolower($sig2)) {

            $this->log($sig . '!=' . $sig2);

            return false;
        }

        return $g;
    }

    public function logout()
    {
        Auth::logout();
        Redirect::to('http://m.my.mail.ru/apps/' . $this->cfg['app_id'])->send();
    }

    public function redirectInApp()
    {
        Redirect::to('http://m.my.mail.ru/apps/' . $this->cfg['app_id'])->send();
    }
}