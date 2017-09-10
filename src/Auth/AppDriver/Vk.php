<?php

namespace Spirit\Auth\AppDriver;

use Spirit\Request;
use Spirit\Auth;

class Vk extends Platform
{

    protected $alias = 'vk';

    public function saveWallPhoto($img = false, $user_id = false)
    {
        if (!$user_id) {
            $user_id = $this->session['params']['viewer_id'];
        }

        $res = $this->apiCall('photos.getWallUploadServer');

        if (!isset($res['response']['upload_url'])) return false;

        $params = array(
            'photo' => curl_file_create($img),
        );
        $res = $this->sendGet($res['response']['upload_url'], $params);

        if (!isset($res['photo'])) return false;

        $params = $res;
        $params['user_id'] = $user_id;

        $res = $this->apiCall('photos.saveWallPhoto', $params);

        if (!isset($res['response']['0']['id'])) return false;

        $return = array(
            'id' => $res['response']['0']['id'],
            'src' => $res['response']['0']['src_big']
        );

        // Возвращаем ссылку
        return $return;
    }

    function apiCall($method, $params = array(), $accessToken = true)
    {
        if ($accessToken && !isset($params['access_token'])) {
            if (!$params) $params = array();

            $params['access_token'] = $this->session['access_token'];
        }

        return $this->sendGet('https://api.vk.com/method/' . $method, $params);
    }

    /**
     *
     * Инициализация пользователя
     *
     */
    protected function getParams()
    {
        if (!Request::has(['viewer_id', 'secret', 'access_token'])) return false;

        $params = array(
            'api_url',
            'api_id',
            'user_id',
            'sid',
            'secret',
            'group_id',
            'viewer_id',
            'is_app_user',
            'is_secure',
            'viewer_type',
            'auth_key',
            'language',
            'parent_language',
            'api_result',
            'api_settings',
            'referrer',
            'access_token',
            'hash',
            'lc_name',
            'ad_info',
        );

        $g = Request::get()->only($params);

        $crc = $g['auth_key'];

        $crc2 = md5($this->cfg['app_id'] . '_' . $g['viewer_id'] . '_' . $this->cfg['secret_key']);

        // проверка подписей
        if (strtolower($crc) !== strtolower($crc2)) return false;

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

    protected function initData($token, $uid)
    {
        // Делаем запрос
        $params = [
            'access_token' => $token,
            'user_ids' => $uid,
            'fields' => implode(',', array_keys($this->cfg['get_fields']))
        ];

        $res = $this->apiCall('users.get', $params);

        if (!$res || !isset($res['response']['0']) || !isset($res['response']['0']['uid'])) {
            $this->error = 'error_send_api';
            return false;
        }

        $res = $res['response']['0'];

        $this->appUserID = $res['uid'];
        $this->appToken = $token;
        $this->appData = $res;
        $this->appUserData = $this->getAppUserData($res);

        return true;
    }

    protected function initAppUser($get)
    {
        return $this->initData($get['access_token'], $get['viewer_id']);
    }

    public function initProviderUser()
    {
        $oAuth = $this->oauth();

        if ($error = $oAuth->getError()) {
            $this->error = $error;
            return null;
        }

        return $this->initData($oAuth->access_token, $oAuth->result['user_id']);
    }

    public function logout()
    {

    }

    public function redirectInApp()
    {

    }
}