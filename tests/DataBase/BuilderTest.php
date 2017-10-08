<?php

namespace Tests\DataBase;

use Tests\TestCase;
use Spirit\DB;
use Spirit\DB as db_n;

/**
 * @covers DB
 */
final class BuilderTest extends TestCase
{

    protected static $__tableName = 'test_builder__users';

    public static function setUpBeforeClass()
    {
        if (!db_n\Schema::hasTable(static::$__tableName)) {
            db_n\Schema::create(static::$__tableName,function(db_n\schema\Table $table) {
                $table->serial('id')
                    ->string('name')
                    ->string('email')
                    ->boolean('is_admin')->default(false);
            });

            $sql = "
                INSERT INTO
                  " . static::$__tableName . " (name,email,is_admin)
                VALUES (?, ?, ?),(?, ?, ?) 
            ";

            DB::execute($sql, [
                'Nuriev Marat',
                "nurieff@gmail.com",
                false,
                'Nuriev Marsele',
                "nurieff@gmail.com",
                true
            ]);
        }
    }

    public static function tearDownAfterClass()
    {
        db_n\Schema::table(static::$__tableName, function (db_n\schema\Table $table) {
            $table->drop();
        });
    }

    protected function setUp()
    {
        DB::beginTransaction();

    }

    protected function tearDown()
    {
        DB::rollback();
    }

//    /**
//     * @return PDO
//     */
//    protected function getConnection()
//    {
//        return DB::connect()->getConnection();
//    }

    public function testBuilder()
    {
        $items = DB::table(static::$__tableName)
            ->where('id',1)
            ->get();

        $this->assertTrue($items instanceof \Spirit\Collection);

        $this->assertCount(1,$items->toArray());
    }

    public function testWhereCallback()
    {
        $items = DB::table(static::$__tableName)
            ->where(function (db_n\Builder\Where $b) {
                $b->where('id',1)->orWhere('id',2);
            })
            ->where('id','>',0)
            ->get();

        $this->assertTrue($items instanceof \Spirit\Collection);
        $this->assertCount(2,$items);
    }

    public function testWhereIn()
    {
        $items = DB::table(static::$__tableName)
            ->whereIn('id',[1,2])
            ->get();

        $this->assertTrue($items instanceof \Spirit\Collection);
        $this->assertCount(2,$items);
    }

    public function testWhereRaw()
    {
        $items = DB::table(static::$__tableName)
            ->whereRaw('id = 1')
            ->get();

        $this->assertTrue($items instanceof \Spirit\Collection);
        $this->assertCount(1,$items);

        $items = DB::table(static::$__tableName)
            ->whereRaw('id = ?',[2])
            ->get();

        $this->assertTrue($items instanceof \Spirit\Collection);
        $this->assertCount(1,$items);
    }

    public function testCount()
    {
        $count = DB::table(static::$__tableName)
            ->where('id','>',0)
            ->count();

        $this->assertEquals(2,$count);
    }

    public function testPaginate()
    {
        $items = DB::table(static::$__tableName)
            ->where('id','>',0)
            ->paginate(1);

        $this->assertCount(1,$items);
    }

    public function testUpdate()
    {
        $c = DB::table(static::$__tableName)
            ->where('id','=',1)
            ->update([
                'is_admin' => true
            ]);

        $this->assertEquals(1,$c);
    }

    public function testUpdateNotFind()
    {
        $c = DB::table(static::$__tableName)
            ->where('id','=',9999)
            ->update([
                'is_admin' => true
            ]);

        $this->assertEquals(0,$c);
    }

    public function testInsert()
    {
        $c = DB::table(static::$__tableName)
            ->insert([
                'name' => 'Ivan Petrov',
                'email' => "ivan@petrov.ru",
                'is_admin' => false,
            ]);

        $c2 = DB::table(static::$__tableName)
            ->insert([
                [
                    'name' => 'Alexey Sidorov',
                    'email' => "alexey@sidorov.ru",
                    'is_admin' => false,
                ],
                [
                    'name' => 'Petr Kuznetsov',
                    'email' => "petr@kuznecov.ru",
                    'is_admin' => false,
                ]
            ]);

        $this->assertEquals(1,$c);
        $this->assertEquals(2,$c2);
    }

    public function testInsertGetId()
    {
        $id = DB::table(static::$__tableName)
            ->insertGetId([
                'name' => 'Vladimir Putin',
                'email' => "vladimir@putin.ru",
                'is_admin' => false,
            ]);

        $this->assertTrue(is_numeric($id));
    }

    public function testInsertGet()
    {
        $item = DB::table(static::$__tableName)
            ->insertGet([
                'name' => 'Vladimir Putin',
                'email' => "vladimir@putin.ru",
                'is_admin' => false,
            ]);

        $this->assertTrue(is_array($item));
        $this->assertArrayHasKey('id',$item);
        $this->assertArrayHasKey('name',$item);
    }

    public function testDelete()
    {
        $id = DB::table(static::$__tableName)
            ->insertGetId([
                'name' => 'Vladimir Putin',
                'email' => "vladimir@putin.ru",
                'is_admin' => false,
            ]);


        $amount = DB::table(static::$__tableName)
            ->where('id', $id)
            ->delete();

        $this->assertEquals(1, $amount);

        $amount2 = DB::table(static::$__tableName)
            ->where('id', $id)
            ->delete();
        $this->assertEquals(0, $amount2);
    }

    public function testDeleteNotFind()
    {
        $amount2 = DB::table(static::$__tableName)
            ->where('id', 9999)
            ->delete();
        $this->assertEquals(0, $amount2);
    }

}
