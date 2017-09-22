<?php

namespace Spirit\Auth;

use Spirit\Engine;
use Spirit\Request\Client;
use Spirit\Request\Cookie;
use Spirit\Request\Session;

/**
 * Class Storage
 * @package Spirit\Auth\DefaultDriver
 *
 * @property  integer $f
 * @property  integer $id
 * @property  integer $version
 * @property  integer $online_time
 * @property  bool $log
 */
class Storage
{
    protected $session;
    protected $data;

    public function __construct()
    {
        $this->session = Session::storage('user');

        if (!$data = $this->fromSession()) {
            $data = $this->fromCookie();

            if ($data) {
                $this->session['id'] = $data['id'];
                $this->session['version'] = $data['version'];
            }
        }

        $this->data = $data ? $data : [];
    }

    protected function fromSession()
    {
        if (!$this->session['id'] || !$this->session['version']) {
            return null;
        }

        return [
            'id' => $this->session['id'],
            'version' => $this->session['version']
        ];
    }

    protected function fromCookie()
    {
        if (!Engine::cfg()->auth['type'] === 'cookie') {
            return null;
        }

        if (!$cookie = Cookie::get('user')) {
            return null;
        }

        $arr = unserialize($cookie);

        if (count($arr) != 3) {
            throw new \Exception('Error count of array auth-cookie');
        }

        $id = $arr[0];
        $version = $arr[1];
        $clientHash = $arr[2];

        if (!hash_equals($clientHash, Client::hash())) {
            return null;
        }

        return [
            'id' => $id,
            'version' => $version
        ];
    }

    public function __get($name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }

        if (isset($this->session[$name])) {
            return $this->session[$name];
        }

        return null;
    }

    public function __set($k, $v)
    {
        $this->data[$k] = $v;
        $this->session[$k] = $v;
    }

    public function setUserCookie($user_id, $version)
    {
        $cookie = serialize([$user_id, $version, Client::hash()]);

        Cookie::set('user', $cookie);
    }

    public function forget()
    {
        Cookie::forget('user');
        Session::forget('user');
    }

    public function save()
    {
        if (!Engine::cfg()->auth['type'] === 'cookie') {
            $this->setUserCookie($this->id, $this->version);
        }
    }
}