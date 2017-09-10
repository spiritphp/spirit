<?php

namespace Spirit\Route\Middleware;

use Spirit\Error;
use Spirit\Request\Client;
use Spirit\Structure\Middleware;

class IpAllow extends Middleware
{

    public function handle($ips = null)
    {
        $client_ip = Client::getIP();
        $client_ip_long = ip2long($client_ip);
        $ipsArr = explode(',', $ips);

        $success = false;
        foreach ($ipsArr as $ip) {
            if (strpos($ip, '-') !== false) {
                $ipArr = explode('-', $ip);
                if (ip2long($ipArr[0]) <= $client_ip_long && $client_ip_long <= ip2long($ipArr[1])) {
                    $success = true;
                }

            } elseif (strpos($ip, '*') !== false) {
                $ip_match = strtr($ip, [
                    '*' => '\d{1,3}',
                    '.' => '\.'
                ]);

                if (preg_match("/^" . $ip_match . "$/", $client_ip)) {
                    $success = true;
                }
            } elseif (hash_equals($client_ip, $ip)) {
                $success = true;

            }

            if ($success) {
                break;
            }
        }

        if ($success) {
            Error::abort(403);
        }

        return $success;
    }

}