<?php

namespace Tests\Model;

use Spirit\Structure\Model;

class TagModel extends Model
{
    protected $table = 'test_relation_model__tags';
    protected $timestamps = false;

    public function books()
    {
        return $this->belongToMany(BookModel::class,'test_relation_model__book_tag','tag_id','book_id');
    }
}