<?php

namespace Tests\Model;

use PHPUnit\Framework\TestCase;
use Spirit\DB;
use Spirit\DB as db_n;

function __getTableNameBooks()
{
    return 'test_relation_model__books';
}

function __getTableNameCategories()
{
    return 'test_relation_model__categories';
}

function __getTableNameTags()
{
    return 'test_relation_model__tags';
}

function __getTableNameBookTag()
{
    return 'test_relation_model__book_tag';
}

/**
 * @covers DB
 */
class RelationTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        if (!db_n\Schema::hasTable(__getTableNameCategories())) {
            db_n\Schema::create(__getTableNameCategories(), function(db_n\schema\Table $table) {
                $table->serial('id')
                    ->timestamps()
                    ->string('name')
                ;
            });
        }

        if (!db_n\Schema::hasTable(__getTableNameTags())) {
            db_n\Schema::create(__getTableNameTags(), function(db_n\schema\Table $table) {
                $table->serial('id')
                    ->timestamps()
                    ->string('name')
                ;
            });
        }

        if (!db_n\Schema::hasTable(__getTableNameBooks())) {
            db_n\Schema::create(__getTableNameBooks(), function(db_n\schema\Table $table) {
                $table->serial('id')
                    ->timestamps()
                    ->softRemove()
                    ->string('title')
                    ->string('author')
                    ->integer('category_id')->index()
                ;

                $table->foreign('category_id')
                    ->on(__getTableNameCategories())
                    ->references('id')
                    ->onDelete('SET NULL')
                    ->onUpdate('CASCADE');
            });
        }

        if (!db_n\Schema::hasTable(__getTableNameBookTag())) {
            db_n\Schema::create(__getTableNameBookTag(), function(db_n\schema\Table $table) {
                $table->serial('id')
                    ->timestamps()
                    ->integer('book_id')->index()
                    ->integer('tag_id')->index()
                    ->integer('status')->default(1)
                ;

                $table->foreign('book_id')
                    ->on(__getTableNameBooks())
                    ->references('id')
                    ;

                $table->foreign('tag_id')
                    ->on(__getTableNameTags())
                    ->references('id')
                    ;
            });
        }

        DB::table(__getTableNameBookTag())->delete();
        DB::table(__getTableNameTags())->delete();
        DB::table(__getTableNameBooks())->delete();
        DB::table(__getTableNameCategories())->delete();

    }

    public static function tearDownAfterClass()
    {
        db_n\Schema::table(__getTableNameBookTag(), function(db_n\schema\Table $table) {
            $table->drop();
        });

        db_n\Schema::table(__getTableNameTags(), function(db_n\schema\Table $table) {
            $table->drop();
        });

        db_n\Schema::table(__getTableNameBooks(), function(db_n\schema\Table $table) {
            $table->drop();
        });

        db_n\Schema::table(__getTableNameCategories(), function(db_n\schema\Table $table) {
            $table->drop();
        });
    }

//    protected function setUp()
//    {
//        DB::beginTransaction();
//
//    }
//
//    protected function tearDown()
//    {
//        DB::rollback();
//    }

    public function testCreate()
    {
        $category = new CategoryModel(['name' => 'Test Category']);
        $category->save();

        $book = new BookModel(['title' => 'Spirit','author' => 'Nuriev Marat']);
        $category->books()->save($book);

        $this->assertEquals($category->id, $book->category_id);

        $amount = DB::table(__getTableNameBooks())
            ->count();
        $this->assertEquals(1, $amount);

        $bookData = DB::table(__getTableNameBooks())
            ->first();

        $this->assertEquals($category->id, $bookData['category_id']);

        $books = $category->books;
        $this->assertTrue($books instanceof \Spirit\Collection);
        $this->assertCount(1, $books);

        return [
            $category,
            $book
        ];
    }

    /**
     * @depends testCreate
     */
    public function testBelongToMany($data)
    {
        /**
         * @var BookModel $book
         * @var CategoryModel $category
         */
        list($category, $book) = $data;

        $tag1 = new TagModel(['name' => 'Tag 1']);
        $tag2 = new TagModel(['name' => 'Tag 2']);
        $tag1->save();
        $tag2->save();

        $book->tags()->save($tag1);
        $book->tags()->save($tag2);

        $tags = $book->tags;

        $this->assertCount(2, $tags);
        $this->assertCount(1, $tag1->books);
        $this->assertCount(1, $tag2->books);


    }

}
