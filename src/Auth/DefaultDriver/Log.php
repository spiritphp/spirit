<?php

namespace Spirit\Auth\DefaultDriver;

use Spirit\Common\Models\User;
use Spirit\DB;
use Spirit\Request\Client;
use Spirit\Common\Models\User\Log as LogModel;

class Logs {

    /**
     * @param User $user
     */
    public static function write($user)
    {
        $ip = Client::getIP();

        if (!isset($_SERVER['HTTP_USER_AGENT'])) {
            $_SERVER['HTTP_USER_AGENT'] = 'Unknown';
        }

        $hash = md5($user->id . $ip . $_SERVER['HTTP_USER_AGENT']);

        /**
         * @var LogModel $log
         */
        $log = $user->logs()->where('hash', $hash)->first();
        if ($log) {
            $log->touch();
        } else {
            $log = LogModel::make([
                'ip' => (DB::isDriver(DB::DRIVER_POSTGRESQL) ? DB::raw("'" . $ip . "'::inet") : $ip),
                'user_agent' => mb_substr($_SERVER['HTTP_USER_AGENT'], 0, 1000, "UTF-8"),
                'hash' => $hash
            ]);

            $user->logs()->save($log);
        }
    }

}