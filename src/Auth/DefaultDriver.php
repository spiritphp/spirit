<?php

namespace Spirit\Auth;

use Spirit\Auth\DefaultDriver\Log;
use Spirit\Auth\DefaultDriver\Password;
use Spirit\Auth\DefaultDriver\Recovery;
use Spirit\Common\Models\User;
use Spirit\DB;
use Spirit\Engine;
use Spirit\Func;

class DefaultDriver extends Driver
{

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

    /**
     * @param integer $id
     * @param bool $remember
     * @return null|User
     */
    public function loginById($id, $remember = false)
    {
        return $this->authorize(['id' => $id], $remember);
    }

    /**
     * @param array $filter
     * @param bool $remember
     * @return null|User
     */
    public function authorize($filter, $remember = false)
    {
        $password = null;
        if (isset($filter['password'])) {
            $password = $filter['password'];
            unset($filter['password']);
        }

        $classModel = static::userModel();

        $query = $classModel::make();

        foreach($filter as $field => $value) {
            $query->where($field, $value);
        }

        /**
         * @var User $user
         */
        if (!$user = $query->first()) {
            return null;
        }

        if ($password && !Password::check($password, $user->password)) {
            return null;
        }

        $this->storage->version = $user->version;
        $this->storage->id = $user->id;

        if ($remember) {
            $this->storage->save();
        }

        return $user;
    }

    /**
     * @param array $fields
     * @param bool $autoAuthorize
     * @param bool $remember
     * @return User
     */
    public function register($fields, $autoAuthorize = true, $remember = false)
    {
        $classModel = static::userModel();

        if (isset($fields['password'])) {
            $fields['password'] = Password::init($fields['password']);
        }

        $user = $classModel::make($fields);
        $user->uid = Func\Func::unique_id(10);
        $user->token = hash('sha256', uniqid(mt_rand(0, 10000000)));
        $user->save();

        if ($autoAuthorize) {
            $this->loginById($user->id, $remember);
        }

        Log::write($user);

        return $user;
    }

    public function setPassword($password)
    {
        $version = uniqid();
        $this->user->password = Password::init($password);

        if ($version) {
            $this->user->version = $version;
            $this->storage->version = $version;
            $this->storage->save();
        }

        $this->user->save();
    }

    /**
     * @return RecoveryInterface
     */
    public function recovery()
    {
        return Recovery::make($this->user);
    }
}