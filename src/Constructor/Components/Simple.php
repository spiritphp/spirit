<?php

namespace Spirit\Constructor\Components;

use Spirit\Structure\Component;

class Simple extends Component
{

    protected $directory = '';

    public static function v($view, $data = false)
    {
        return static::getInstance()->view($view, $data);
    }

}