<?php

namespace Spirit\Auth\AppDriver;

/**
 * Class User
 *
 * @property string $token
 * @property string $id
 * @property string $alias
 * @property array $all
 * @property string $first_name
 * @property string $last_name
 * @property string $birthday
 * @property string $picture
 * @property int $gender
 */
class User
{

    protected $data = [];

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function __get($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }
}