<?php

namespace Spirit\Request;

use Spirit\Services\Crypt;
use Spirit\Engine;
use Spirit\Func;

class CookieVariables extends Variables {

    protected $cryptPrefix = '%%';

    public function __construct(array $data = [])
    {
        parent::__construct($data);

        $this->unlock();
    }

    /**
     * @param null $k
     * @param null $default
     * @return $this|static|FileVariables|mixed|null
     */
    public function get($k = null, $default = null)
    {
        $value = parent::get($k, $default);

        if ($value && !is_object($value) && strpos($value, $this->cryptPrefix) === 0) {
            if (substr($value, 0, strlen($this->cryptPrefix)) === $this->cryptPrefix) {
                $value = substr($value, strlen($this->cryptPrefix));
            }
            $value = Crypt::decrypt($value);
        }

        return $value;
    }

    protected function setCookie($key, $value = null, $minutes = 52560, $path = '/', $domain = null, $secure = null, $httpOnly = true)
    {
        if ($this->isLock === true) {
            throw new \Exception('Lock box');
        }

        if (is_string($minutes)) {
            $seconds = Func\Date::secondFromText($minutes);
        } else {
            $seconds = $minutes * 60;
        }

        if ($seconds < 0) {
            $time = 0;
        } else {
            $time = time() + $seconds;
        }

        if (is_null($secure)) {
            $secure = Engine::i()->isSSL;
        }

        setcookie($key, $value, $time, $path, $domain, $secure, $httpOnly);

        $this->data[$key] = $value;
    }

    /**
     * @param array|string $key
     * @param null $value
     * @param int $minutes
     * @param string $path
     * @param null|string $domain
     * @param null|bool $secure
     * @param bool $httpOnly
     * @throws \Exception
     */
    public function setSimple($key, $value = null, $minutes = 52560, $path = '/', $domain = null, $secure = null, $httpOnly = true)
    {
        $this->setCookie($key, $value, $minutes, $path, $domain, $secure, $httpOnly);
    }

    /**
     * @param array|string $key
     * @param null $value
     * @param int $minutes
     * @param string $path
     * @param null|string $domain
     * @param null|bool $secure
     * @param bool $httpOnly
     * @throws \Exception
     */
    public function set($key, $value = null, $minutes = 52560, $path = '/', $domain = null, $secure = null, $httpOnly = true)
    {
        $this->setCookie($key, '%%' . Crypt::encrypt($value), $minutes, $path, $domain, $secure, $httpOnly);
    }

    public function forget()
    {
        if ($this->isLock === true) {
            throw new \Exception('Lock cookie');
        }

        $keys = Func\Arr::fromArgs(func_get_args());

        foreach($keys as $key) {
            if (array_key_exists($key, $this->data)) {
                static::setSimple($key, null, -1);
                unset($_COOKIE[$key]);
                unset($this->data[$key]);
            }
        }
    }
}