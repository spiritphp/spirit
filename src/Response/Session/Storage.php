<?php

namespace Spirit\Response\Session;

use Spirit\Response\Session;
use Spirit\Structure\Arrayable;

class Storage implements \ArrayAccess, Arrayable {

    protected $key;
    protected $data = [];

    public function __construct($key, $data = [])
    {
        $this->key = $key;
        $this->data = is_array($data) ? $data : [];
    }

    public function save()
    {
        Session::set($this->key, $this->data);
    }

    public function clean()
    {
        Session::forget($this->key);
    }

    public function __set($name, $value)
    {
        if (is_null($name)) {
            $this->data[] = $value;
        } else {
            $this->data[$name] = $value;
        }

        $this->save();
    }

    public function __get($name)
    {
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }

    public function toArray()
    {
        $this->data;
    }

    public function offsetSet($offset, $value)
    {
        $this->__set($offset, $value);
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }
}