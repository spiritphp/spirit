<?php

namespace Spirit\Func;

class Func
{

    static public function unique_id($l = 8)
    {
        return substr(md5(uniqid(mt_rand(), true)), 0, $l);
    }

}
