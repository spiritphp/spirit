<?php

namespace Tests\Model;

use Spirit\Structure\Model;

class BookModel extends Model
{
    use Model\SoftRemoveTrait;
    protected $table = 'test_relation_model__books';

    public function category()
    {
        return $this->belongTo(CategoryModel::class,'category_id','id');
    }

    public function tags()
    {
        return $this->belongToMany(TagModel::class,'test_relation_model__book_tag','book_id','tag_id');
    }
}