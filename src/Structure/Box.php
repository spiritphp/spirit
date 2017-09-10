<?php

namespace Spirit\Structure;

use Spirit\Func\Arr;

class Box implements Arrayable
{
    protected $data = [];
    protected $hidden = [];
    protected $isLock = false;

    public static function make($data = [])
    {
        return new static($data);
    }

    public function __construct($data = [])
    {
        $this->data = $data;
    }

    public function __get($k)
    {
        return $this->get($k);
    }

    public function __set($k, $v)
    {
        $this->set($k, $v);
    }

    public function all()
    {
        return $this->data;
    }

    public function except()
    {
        $keys = Arr::fromArgs(func_get_args());

        $newArray = [];

        foreach($this->data as $k => $v) {
            if (in_array($k, $keys, true)) {
                continue;
            }

            $newArray[$k] = $v;
        }

        return $newArray;
    }

    public function only()
    {
        $keys = Arr::fromArgs(func_get_args());

        $newArray = [];
        foreach($keys as $key) {
            $newArray[$key] = $this->get($key);
        }

        return $newArray;
    }

    public function has()
    {
        $keys = Arr::fromArgs(func_get_args());

        foreach ($keys as $key) {
            if (!Arr::exists($this->data, $key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        if (in_array($key, $this->hidden)) {
            return null;
        }

        return array_key_exists($key, $this->data) ? $this->data[$key] : $default;
    }

    /**
     * @param string|array $key
     * @param mixed|null $value
     * @throws \Exception
     */
    public function set($key, $value = null)
    {
        if ($this->isLock === true) {
            throw new \Exception('Lock box');
        }

        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }

    }

    public function hidden($arr)
    {
        if (!is_array($arr)) {
            $arr = [$arr];
        }

        $this->hidden = array_unique(array_merge($this->hidden, $arr));

        return $this;
    }

    public function visible($arr)
    {
        if (!is_array($arr)) {
            $arr = [$arr];
        }

        $this->hidden = array_diff($this->hidden, $arr);

        return $this;
    }

    public function forget()
    {
        if ($this->isLock === true) {
            throw new \Exception('Lock box');
        }

        $keys = Arr::fromArgs(func_get_args());

        foreach($keys as $key) {
            if (array_key_exists($key, $this->data)) {
                unset($this->data[$key]);
            }
        }
    }

    public function toArray()
    {
        return $this->data;
    }

    public function lock()
    {
        $this->isLock = true;
    }

    public function unlock()
    {
        $this->isLock = false;
    }
}