<?php

namespace Spirit\Services;

use Spirit\Func;

class Send
{
    const POST = 'POST';
    const GET = 'GET';
    const PUT = 'PUT';
    const DELETE = 'DELETE';

    protected static $isFlow = false;
    protected static $currentCh;

    public static function openFlow()
    {
        static::$isFlow = true;
    }

    public static function closeFlow()
    {
        static::$isFlow = false;
        if (static::$currentCh) {
            curl_close(static::$currentCh);
        }
    }

    protected static function exec($method, $url, $params = [])
    {
        if (in_array($method, [static::DELETE, static::GET])) {
            if ($params && is_array($params)) {
                $params = http_build_query($params);
            }

            if ($params) {
                $url .= (strpos($url, '?') === false ? '?' : '&') . $params;
            }
        }

        $headers = [];

        if (static::$isFlow) {
            if (!static::$currentCh) {
                static::$currentCh = curl_init();
            }

            $ch = static::$currentCh;
        } else {
            $ch = curl_init();
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        switch ($method) {
            case static::POST:
            case static::PUT:
                curl_setopt($ch, CURLOPT_POST, 1);

                if (is_string($params) && Func\Str::isJson($params)) {
                    $headers[] = "Content-type: application/json";
                } else if (is_array($params)) {
                    $params = http_build_query($params);
                }

                $headers[] = "Content-Length: " . mb_strlen($params, "UTF-8");

                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                break;
            case static::DELETE:
            case static::GET:
            default:
                break;
        }

        if (in_array($method, [static::DELETE, static::PUT])) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        if (count($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        try {
            $result = curl_exec($ch);
        } catch (\Exception $e) {
            if (!static::$isFlow) {
                curl_close($ch);
            }
            return null;
        }

        if (!static::$isFlow) {
            curl_close($ch);
        }

        return $result;
    }

    public static function get($url, $params = [])
    {
        return static::exec(static::GET, $url, $params);
    }

    public static function post($url, $params)
    {
        return static::exec(static::POST, $url, $params);
    }

    public static function put($url, $params)
    {
        return static::exec(static::PUT, $url, $params);
    }

    public static function delete($url, $params)
    {
        return static::exec(static::DELETE, $url, $params);
    }

}