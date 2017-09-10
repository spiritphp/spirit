<?php

namespace Spirit\Structure;

interface Jsonable
{
    /**
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0);
}