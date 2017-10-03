<?php

namespace Spirit\Services\Validator;

use ArrayAccess;
use Spirit\Structure\Arrayable;

class ErrorMessages implements Arrayable, \Countable, \IteratorAggregate, ArrayAccess {

    protected $errors;

    public function __construct($errors)
    {
        $this->errors = $errors;
    }

    /**
     * @return array
     */
    public function all()
    {
        $arr = [];
        foreach($this->errors as $error) {
            if (is_array($error)) {
                $arr = array_merge($arr, array_values($error));
            } else {
                $arr[] = $error;
            }
        }

        return $arr;
    }

    public function join($sep = '<br/>')
    {
        return implode($sep, $this->all());
    }

    public function get($attr)
    {
        return isset($this->errors[$attr]) && count($this->errors[$attr]) ? $this->errors[$attr] : null;
    }

    public function first($attr)
    {
        $error = $this->get($attr);

        return $error ? $error[0] : null;
    }

    public function has($attr)
    {
        return !!$this->get($attr);
    }

    public function toArray()
    {
        return $this->errors;
    }

    public function count()
    {
        return count($this->errors);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->errors);
    }

    public function offsetSet($offset, $value)
    {

    }

    public function offsetExists($offset)
    {
        return isset($this->errors[$offset]);
    }

    public function offsetUnset($offset)
    {

    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }
}