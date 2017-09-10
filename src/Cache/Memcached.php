<?php

namespace Spirit\Cache;

use \Memcached as M;

class Memcached extends Store {

    protected $servers = [];
    protected $prefix;

    /**
     * @var boolean
     */
    protected $isVersionThree;

    /**
     * @var M
     */
    protected $m;

    protected function init()
    {
        $this->servers = $this->config['servers'];

        if (isset($this->config['prefix']) && !is_null($this->config['prefix'])) {
            $this->prefix = $this->config['prefix'] . ':';
        } else {
            $this->prefix = SPIRIT_KEY . ':';
        }

        $this->m = new M();

        foreach($this->servers as $server) {
            $this->m->addServer(
                $server['host'], $server['port'], (isset($server['weight']) ? $server['weight'] : 0)
            );
        }

        $this->isVersionThree = (new \ReflectionMethod('Memcached', 'getMulti'))
                ->getNumberOfParameters() == 2;

        $this->validateConnection();
    }

    protected function validateConnection()
    {
        $status = $this->m->getVersion();

        if (! is_array($status)) {
            throw new \Exception('No Memcached servers added.');
        }

        if (in_array('255.255.255', $status) && count(array_unique($status)) === 1) {
            throw new \Exception('Could not establish Memcached connection.');
        }
    }

    protected function getKey($k)
    {
        return $this->prefix . $k;
    }

    public function has($key)
    {
        if (static::get($key)) {
            return true;
        }

        return false;
    }

    public function get($key)
    {
        if (isDebug()) {
            $d = debug_backtrace();
            $_file = $d[0]['file'];
            $_line = $d[0]['line'];
            $map = $_file . ':' . $_line;

            $this->statGet[] = [
                'key' => $this->getKey($key),
                'map' => $map
            ];
        }

        $value = $this->m->get($this->getKey($key));

        if ($this->m->getResultCode() == 0) {
            return $value;
        }

        return null;
    }

    public function many(array $keys)
    {
        $prefixedKeys = array_map(function ($key) {
            return $this->getKey($key);
        }, $keys);

        if (isDebug()) {
            $d = debug_backtrace();
            $_file = $d[0]['file'];
            $_line = $d[0]['line'];
            $map = $_file . ':' . $_line;

            $this->statGet[] = [
                'key' => implode(',',$prefixedKeys),
                'map' => $map
            ];
        }

        if ( $this->isVersionThree) {
            $values = $this->m->getMulti($prefixedKeys, M::GET_PRESERVE_ORDER);
        } else {
            $null = null;

            $values = $this->m->getMulti($prefixedKeys, $null, M::GET_PRESERVE_ORDER);
        }

        if ($this->m->getResultCode() != 0) {
            return array_fill_keys($keys, null);
        }

        return array_combine($keys, $values);
    }

    public function put($key, $value, $minutes = 0)
    {
        if (isDebug()) {
            $d = debug_backtrace();
            $_file = $d[0]['file'];
            $_line = $d[0]['line'];
            $map = $_file . ':' . $_line;

            $this->statPut[] = [
                'key' => $key,
                'exp' => $this->toTimestamp($minutes),
                'map' => $map
            ];
        }

        $this->m->set($this->getKey($key), $value, $this->toTimestamp($minutes));
    }

    public function putMany(array $values, $minutes)
    {
        $prefixedValues = [];

        foreach ($values as $key => $value) {
            $prefixedValues[$this->getKey($key)] = $value;
        }

        if (isDebug()) {
            $d = debug_backtrace();
            $_file = $d[0]['file'];
            $_line = $d[0]['line'];
            $map = $_file . ':' . $_line;

            $this->statPut[] = [
                'key' => implode(',', array_keys($prefixedValues)),
                'exp' => $this->toTimestamp($minutes),
                'map' => $map
            ];
        }

        $this->m->setMulti($prefixedValues, $this->toTimestamp($minutes));
    }

    public function forever($key, $value)
    {
        $this->put($key, $value, 0);
    }

    public function pull($key)
    {
        if ($value = $this->get($key)) {
            $this->forget($key);
            return $value;
        }

        return null;
    }

    public function forget($key)
    {
        return $this->m->delete($this->getKey($key));
    }

    public function flush()
    {
        return $this->m->flush();
    }

}