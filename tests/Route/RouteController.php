<?php

namespace Tests\Route;

use Spirit\Structure\Controller;

class RouteController extends Controller {

    public function test(DispatcherUserModel $userModel)
    {
        return $userModel->name;
    }

}