<?php

namespace Spirit\Request;

use Spirit\Cache;

class Throttle
{

    public static function make($amount = 60, $timeout = 1)
    {
        return new static($amount, $timeout);
    }

    protected $amount;
    protected $timeout;
    protected $key = null;

    /**
     * @var array
     */
    protected $cacheData;

    public function __construct($amount = 60, $timeout = 1)
    {
        $this->amount = $amount;
        $this->timeout = $timeout;
    }

    protected function cache()
    {
        if (!$this->cacheData) {
            $ip = Client::getIP();
            $url = URL::path();

            $this->key = 'throttle_' . md5($ip . $url);

            $this->cacheData = Cache::get($this->key);

            if (!$this->cacheData) {
                $this->cacheData = [
                    'c' => 0
                ];
            }
        }

        return $this->cacheData;
    }

    protected function write($minutes)
    {
        Cache::put($this->key, $this->cacheData, $minutes);
    }

    protected function touch()
    {
        $cache = $this->cache();
        $cache['c'] += 1;
        $this->cacheData = $cache;

        $this->write(1);
    }

    protected function stop()
    {
        $cache = $this->cache();
        $cache['stop'] = time() + $this->timeout * 60;

        $this->cacheData = $cache;

        $this->write($this->timeout);
    }

    protected function reset()
    {
        $this->cacheData = [
            'c' => 0
        ];
    }

    public function check()
    {
        $cache = $this->cache();

        if (isset($cache['stop'])) {
            if ($cache['stop'] > time()) {
                return false;
            }

            $this->reset();
        }

        if ($cache['c'] >= $this->amount) {
            $this->stop();
            return false;
        }

        $this->touch();

        return true;
    }

    public function retryAfter()
    {
        $cache = $this->cache();

        return $cache['stop'] - time();
    }

    public function rateLimitReset()
    {
        $cache = $this->cache();

        return $cache['stop'];
    }

    public function limit()
    {
        return $this->amount;
    }

    public function remaining()
    {
        $cache = $this->cache();

        return $this->amount - $cache['c'];
    }
}