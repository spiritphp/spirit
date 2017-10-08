<?php

namespace Tests\Model;

use Spirit\Structure\Model;

class CategoryModel extends Model
{
    protected $table = 'test_relation_model__categories';
    protected $timestamps = false;

    public function books()
    {
        return $this->hasMany(BookModel::class,'category_id','id');
    }
}