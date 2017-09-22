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
        return new static($user);
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
     * @param User $user
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    public function init()
    {
        $token = Hash::h256([$this->user->id, uniqid()]);

        $recovery = new User\Recovery();
        $recovery->ip = Client::getIP();
        $recovery->token = $token;

        $this->user->recoveries()->save($recovery);

        $this->recovery = $recovery;

        return $this;
    }

    /**
     * @return User\Recovery
     */
    public function get()
    {
        return $this->recovery;
    }

    public function use()
    {
        if (!$this->recovery) {
            throw new RecoveryException('Recovery is not init');
        }

        $this->recovery->used_at = DB::raw('NOW()');
        $this->recovery->ip_used = Client::getIP();
        $this->recovery->save();
    }

    public function initForToken($token)
    {
        /**
         * @var User\Recovery $recovery
         */
        $recovery = User\Recovery::where('token',$token)->first();

        if (!$recovery) {
            return $this;
        }

        if ($recovery->used_at) {
            return $this;
        }

        $this->recovery = $recovery;

        return $this;
    }
}