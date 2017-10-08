<?php

namespace Tests\Route;

use Spirit\Structure\Model;

class DispatcherUserModel extends Model
{
    use Model\SoftRemoveTrait;
    protected $table = 'test_route_dispatcher_model__users';

}