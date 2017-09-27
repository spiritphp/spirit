<?php

namespace Spirit\Auth\DefaultDriver;

use Spirit\Auth\RecoveryException;
use Spirit\Auth\RecoveryInterface;
use Spirit\Common\Models\User;
use Spirit\DB;
use Spirit\Func\Hash;
use Spirit\Request\Client;

class Recovery implements RecoveryInterface {

    /**
     * @param User $user
     * @return static
     */
    public static function user($user)
    {
        return static::make()->setUser($user);
    }

    /**
     * @param $token
     * @param int $lifeminute
     * @return null|static
     */
    public static function token($token, $lifeminute = 60)
    {
        /**
         * @var User\Recovery $recovery
         */
        $recovery = User\Recovery::where('token',$token)->first();

        if (!$recovery) {
            return null;
        }

        if ($recovery->used_at) {
            return null;
        }

        if ((time() - strtotime($recovery->created_at)) > $lifeminute * 60) {
            return null;
        }

        return static::make($recovery);
    }

    /**
     * @param User\Recovery $recovery
     * @return static
     */
    public static function make($recovery = null)
    {
        return new static($recovery);
    }

    /**
     * @var User
     */
    protected $user;

    /**
     * @var User\Recovery
     */
    protected $recovery;

    /**
     * Recovery constructor.
     * @param User\Recovery $recovery
     */
    public function __construct($recovery = null)
    {
        $this->recovery = $recovery;

        if ($this->recovery) {
            $this->user = $this->recovery->user;
        }
    }

    public function setUser(User $user)
    {
        if ($this->user) {
            throw new RecoveryException('The user is already specified');
        }

        $this->user = $user;
        return $this;
    }

    protected function init()
    {
        if (!$this->user) {
            throw new RecoveryException('The user is not specified');
        }

        if ($this->recovery) {
            throw new RecoveryException('The recovery is already initialized');
        }

        $token = Hash::h256([$this->user->id, uniqid()]);

        $recovery = new User\Recovery();
        $recovery->ip = Client::getIP();
        $recovery->token = $token;

        $this->user->recoveries()->save($recovery);

        return $recovery;
    }

    /**
     * @return User\Recovery
     */
    public function get()
    {
        if (!$this->recovery) {
            $this->recovery = $this->init();
        }

        return $this->recovery;
    }

    public function use()
    {
        if (!$this->recovery) {
            throw new RecoveryException('Recovery is not initialized');
        }

        $this->recovery->used_at = DB::raw('NOW()');
        $this->recovery->ip_used = Client::getIP();
        $this->recovery->save();
    }

}