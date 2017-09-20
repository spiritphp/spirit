<?php

namespace Spirit\Auth;

use Spirit\Common\Models\User\Log;
use Spirit\Config\Cfg;
use Spirit\DB;
use Spirit\Request\Client;
use Spirit\Request\Cookie;
use Spirit\Response\Redirect;
use Spirit\Response\Session;
use App\Models\User;

class DefaultDriver extends Driver {

    use Cfg;

    /**
     * @var Session\Storage
     */
    protected $session;

    /**
     * @var User|\Spirit\Common\Models\User
     */
    protected $user;

    protected $authFromSession = false;


    public function __construct()
    {
        $this->session = Session::storage('user');
    }

    protected function attemptAuth()
    {
        if (!$requestInfo = $this->requestInfo()) {
            return;
        }

        $this->user = $this->initUser($requestInfo['id'], $requestInfo['version']);
        $this->setOnline(true);
        $this->log();

        $this->session['id'] = $this->user->id;
        $this->session['version'] = $this->user->version;

        // Установка куки
        if ($this->cfg()->auth['type'] === 'cookie' && !$this->authFromSession) {
            static::setUserCookie($this->user->id, $this->user->version);
        }
    }

    protected function requestBySession()
    {
        if (!$this->session['id'] || !$this->session['version']) {
            return null;
        }

        return [
            'id' => $this->session['id'],
            'version' => $this->session['version']
        ];
    }

    protected function requestByCookie()
    {
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
            $this->logout();
            Redirect::to('/?error_browser')->send();
            return null;
        }

        return [
            'id' => $id,
            'version' => $version
        ];
    }

    protected function requestInfo()
    {
        if ($info = $this->requestBySession()) {
            $this->authFromSession = true;
            return $info;
        }

        if ($this->cfg()->auth['type'] !== 'cookie') {
            return null;
        }

        return $this->requestByCookie();
    }

    protected function initUser($id, $version)
    {
        $user = User::find($id);

        if (!$user) {
            Redirect::to('/?error_user')->send();
            return null;
        }

        if (!hash_equals($user->version, $version)) {
            Redirect::to('/?error_version')->send();
            return null;
        }

        return $user;
    }

    public function setOnline($isOnline = true)
    {
        if ($isOnline) {
            if (
                isset($this->session['online_time']) &&
                (time() - $this->session['online_time']) < $this->cfg()->auth['upd_online_per_time']
            ) {
                return;
            }
        }

        $this->user->date_online = DB::raw('NOW()');
        $this->user->save();

        $this->session['online_time'] = time();
    }

    protected function log()
    {
        if (!static::cfg()->auth['log']) return;

        if (isset($this->session['log'])) return;

        $ip = Client::getIP();

        if (!isset($_SERVER['HTTP_USER_AGENT'])) {
            $_SERVER['HTTP_USER_AGENT'] = 'Unknown';
        }

        $hash = md5($this->user->id . $ip . $_SERVER['HTTP_USER_AGENT']);

        /**
         * @var Log $log
         */
        $log = $this->user->logs()->where('hash',$hash)->first();

        if ($log) {
            $log->touch();
        } else {
            $log = Log::make([
                'ip' => (DB::isDriver(DB::DRIVER_POSTGRESQL) ? DB::raw("'" . $ip . "'::inet") : $ip),
                'user_agent' => mb_substr($_SERVER['HTTP_USER_AGENT'], 0, 1000, "UTF-8"),
                'hash' => $hash
            ]);

            $this->user->logs()->save($log);
        }

        $this->session['log'] = true;
    }

    public function check()
    {
        return !is_null($this->user);
    }

    public function guest()
    {
        return is_null($this->user);
    }

    public function id()
    {
        return $this->user->id;
    }

    public function user()
    {
        return $this->user;
    }

    public function loginById()
    {
        // TODO: Implement loginById() method.
    }

    public function logout()
    {
        $this->setOnline(false);

        Cookie::forget('user');
        Session::forget('user');
    }

    public function init()
    {
        $this->attemptAuth();
    }

    public function setUserCookie($user_id, $version = null)
    {
        if (!$version) {
            $user = User::find($user_id);
            $version = $user->version;
        }

        $cookie = serialize([$user_id,$version,Client::hash()]);

        Cookie::set('user', $cookie);
    }
}