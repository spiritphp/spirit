<?php

namespace Spirit\DB;

class Raw
{

    protected $str = '';

    public static function make($str)
    {
        return new Raw($str);
    }

    public function __construct($str)
    {
        $this->str = $str;
    }

    public function __toString()
    {
        return $this->str;
    }
}