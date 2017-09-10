<?php

namespace Spirit\Request;

use Spirit\Structure\Box;

class Variables extends Box {

    public function __construct(array $data = [])
    {
        parent::__construct($data);

        $this->lock();
    }

    /**
     * @param null $k
     * @param null $default
     * @return $this|static|FileVariables|mixed|null
     */
    public function get($k = null, $default = null)
    {
        if (is_null($k)) {
            return $this;
        }

        return parent::get($k, $default);
    }

}