<?php

namespace App\Controllers\Undercover;

abstract class Controller extends \Spirit\Structure\Controller {

    public function adminview($view = null, $data = null)
    {
        Admin::init();

        return $this->view($view, $data);
    }

}