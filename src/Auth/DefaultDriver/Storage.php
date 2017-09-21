<?php

namespace Spirit\Auth\DefaultDriver;

use Spirit\Request\Session;

class Storage {

    protected $session;

    public function __construct()
    {
        $this->session = Session::storage('user');
    }

}