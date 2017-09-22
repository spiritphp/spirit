<?php

namespace Spirit\Auth;

use Spirit\Auth\DefaultDriver\Log;
use Spirit\Auth\DefaultDriver\Password;
use Spirit\Auth\DefaultDriver\Storage;
use Spirit\Common\Models\User;
use Spirit\DB;
use Spirit\Engine;

class DefaultDriver extends Driver {

    /**
     * @var Storage
     */
    protected $storage;

    public function __construct()
    {
        $this->storage = new Storage();
    }

    public function init()
    {
        if (!$this->storage->id) return;

        if (!$this->user = $this->initUser()) {
            return;
        }

        $this->setOnline(true);
        $this->log();
    }

    protected function initUser()
    {
        $userModel = static::userModel();

        /**
         * @var User
         */
        $user = $userModel::find($this->storage->id);

        if (!$user) {
            return null;
        }

        if (!hash_equals($user->version, $this->storage->version)) {
            return null;
        }

        return $user;
    }

    public function setOnline($isOnline = true)
    {
        if ($isOnline) {
            $onlineTime = $this->storage->online_time;

            if ($onlineTime && (time() - $onlineTime) < Engine::cfg()->auth['upd_online_per_time']) {
                return;
            }
        }

        $this->user->date_online = DB::raw('NOW()');
        $this->user->save();
        $this->storage->online_time = time();
    }

    protected function log()
    {
        if (!Engine::cfg()->auth['log']) return;

        if ($this->storage->log) return;

        Log::write($this->user);

        $this->storage->log = true;
    }

    public function logout()
    {
        $this->setOnline(false);
        $this->user = null;
        $this->storage->forget();
    }

    public function loginById($id, $remember = false)
    {
        return $this->authorize(['id' => $id], $remember);
    }

    public function authorize($filter, $remember = false)
    {

    }

    public function register($filter, $autoAuthorize = true, $remember = false)
    {
        // TODO: Implement register() method.
    }

    public function setPassword($password, $version = null)
    {
        if ($version === true) {
            $version = mt_rand(0,9999999999);
        }

        $this->user->password = Password::init($password);

        if ($version) {
            $this->user->version = $version;
            $this->storage->version = $version;
            $this->storage->save();
        }

        $this->user->save();
    }
}