<?php

namespace Tests\Model;

use Spirit\Structure\Model;

class UserModel extends Model
{
    use Model\SoftRemoveTrait;
    protected $table = 'test_base_model__users';
}