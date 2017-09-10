<?php

namespace Spirit\Request;

class HeaderVariables extends Variables {

    /**
     * HeaderVariables constructor.
     * @param array $data is $_SERVER
     */
    public function __construct(array $data = [])
    {
        $headers = [];
        foreach ($data as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $headers[substr($key, 5)] = $value;
            }
        }

        parent::__construct($headers);
    }

}