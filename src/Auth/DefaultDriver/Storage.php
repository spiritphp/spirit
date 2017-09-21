<?php

namespace Spirit\Auth\DefaultDriver;

use Spirit\Response\Session;

class Storage {

    protected $session;

    public function __construct()
    {
        $this->session = Session::storage('user');
    }

}