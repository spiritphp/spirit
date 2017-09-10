<?php

namespace Spirit\Structure;

abstract class Middleware extends Single
{

    /**
     * @param null $var
     * @return false|true
     */
    abstract function handle($var = null);

}