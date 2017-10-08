<?php

namespace Tests\Model;

use Spirit\Structure\Model;

class UserSecondModel extends Model
{
    use Model\SoftRemoveTrait;
    protected $table = 'test_base_model__users';
    protected $hidden = ['email','updated_at','created_at','removed_at'];
    protected $visible = ['test_param'];

    public function getTestParamData()
    {
        return 'is_test_param';
    }

}