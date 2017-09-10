<?php

namespace Spirit\Route;
use Spirit\Structure\Arrayable;
use Spirit\Structure\Jsonable;

/**
 * Class Current
 * @package Spirit\Route
 *
 * @property array $call
 * @property array $vars
 * @property array $binds
 * @property string $path
 * @property array $config
 * @property bool $callable
 * @property string $alias
 * @property string $className
 * @property string $methodName
 *
 */
class Current implements \ArrayAccess, Arrayable, Jsonable, \JsonSerializable {

    public static function make($data)
    {
        return new static($data);
    }

    /**
     * @var array
     */
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;

        if (is_array($this->data['call'])) {
            $this->data['callable'] = false;
            $this->data['className'] = $this->data['call'][0];
            $this->data['methodName'] = $this->data['call'][1];
        } else {
            $this->data['callable'] = true;
        }

        $this->data['alias'] = $this->data['config']['alias'];
    }

    public function __get($k)
    {
        if (!isset($this->data[$k])) {
            throw new \Exception('CurrentRoute\'s property is not found');
        }

        return $this->data[$k];
    }

    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    public function offsetGet($offset)
    {
        return $this->$offset;
    }


    public function offsetSet($offset, $value)
    {
        return;
    }

    public function offsetUnset($offset)
    {
        return;
    }

    public function __debugInfo()
    {
        return $this->data;
    }

    public function toArray()
    {
        return $this->data;
    }

    public function toJson($options = 0)
    {
        return json_encode($this->data, $options);
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }
}