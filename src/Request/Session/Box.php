<?php

namespace Spirit\Request\Session;

use Spirit\Func\Arr;
use Spirit\Func\Hash;

class Box extends \Spirit\Structure\Box
{

    /**
     * @var array|Storage[]
     */
    protected $storageList = [];

    public function __construct($data = [])
    {
        parent::__construct($data);

        if (!isset($this->data['_once'])) {
            $this->data['_once'] = [];
        }

        $this->data['_clean'] = $this->data['_once'];
        $this->data['_once'] = [];

        $this->initToken();
    }

    protected function initToken()
    {
        if ($this->get('_token')) {
            return;
        }

        $this->set('_token', Hash::h(uniqid(rand(), true)));
    }

    public function get($key, $default = null)
    {
        if (in_array($key, $this->hidden)) {
            return null;
        }

        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }

        return array_key_exists($key, $this->data['_clean']) ? $this->data['_clean'][$key] : $default;
    }

    public function all()
    {
        $arr = array_merge($this->data, $this->data['_clean']);

        unset($arr['_clean']);

        return $arr;
    }

    public function except()
    {
        $keys = Arr::fromArgs(func_get_args());
        $arr = parent::except($keys);

        foreach($this->data['_clean'] as $k => $v) {
            if (in_array($k, $keys, true)) {
                continue;
            }

            $arr[$k] = $v;
        }

        return $arr;
    }

    public function has()
    {
        $keys = Arr::fromArgs(func_get_args());

        foreach($keys as $key) {
            if (!Arr::exists($this->data, $key) && !Arr::exists($this->data['_clean'], $key)) {
                return false;
            }
        }

        return true;
    }

    public function forget()
    {
        $keys = Arr::fromArgs(func_get_args());

        parent::forget($keys);

        foreach($this->data['_clean'] as $k => $v) {
            if (in_array($k, $keys, true)) {
                unset($this->data[$k]);
            }
        }
    }

    public function once($k, $v = null)
    {
        if ($this->isLock === true) {
            throw new \Exception('Lock box');
        }

        if (is_array($k)) {
            $this->data['_once'] = array_merge($this->data['_once'], $k);
        } else {
            $this->data['_once'][$k] = $v;
        }
    }

    public function storage($k, $storageClass = null)
    {
        if (isset($this->storageList[$k])) {
            return $this->storageList[$k];
        }

        if ($storageClass && $storageClass instanceof Storage) {
            $s = new $storageClass($k, $this->get($k));
        } else {
            $s = new Storage($k, $this->get($k));
        }

        return $this->storageList[$k] = $s;
    }

    public function clean()
    {
        $this->storageList = [];
        $this->data = [];
    }

    public function token()
    {
        return $this->get('_token');
    }
}