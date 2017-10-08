<?php

namespace Tests\Route;

use Spirit\Structure\Model;

class UserModel extends Model
{
    use Model\SoftRemoveTrait;
    protected $table = 'test_route_model__users';

}