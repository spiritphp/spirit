<?php

namespace Tests\DataBase;

use Tests\TestCase;
use Spirit\DB;
use Spirit\DB\Schema;
use Spirit\DB\Schema\Table;

/**
 * @covers DB
 */
final class InsertOrUpdateTest extends TestCase
{

    protected function setUp()
    {
        DB::beginTransaction();

    }

    protected function tearDown()
    {
        DB::rollback();
    }

    public function testInsertOrUpdate()
    {
        $table = 'test_insert_or_update__base';
        Schema::create($table,function(Table $table) {
            $table->serial('id')
                ->integer('user_id')
                ->integer('role_id')
                ->integer('status')->default(1);

            $table->unique(['user_id','role_id']);
        });

        $this->assertTrue(Schema::hasTable($table));

        $amount = DB::table($table)->insert([
            [
                'user_id' => 1,
                'role_id' => 1
            ],
            [
                'user_id' => 1,
                'role_id' => 2
            ],
        ]);

        $this->assertEquals(2, $amount);

        $s = DB::insertOrUpdate($table)
            ->columns('user_id','role_id','status')
            ->unique('user_id','role_id')
            ->insert([
                [
                    1, 1, 1
                ]
            ])
            ->update([
                'status' => 2
            ])
            ->exec();
        ;

        $user = DB::table($table)
            ->where('user_id',1)->where('role_id',1)->first();

        $this->assertTrue(!is_null($user));
        $this->assertInternalType('array',$user);
        $this->assertEquals(2,$user['status']);

        DB::insertOrUpdate($table)
            ->columns('user_id','role_id','status')
            ->unique('user_id','role_id')
            ->insert([
                [
                    1, 2, 5
                ]
            ])
            ->update([
                'status'
            ])
            ->exec();
        ;

        $user = DB::table($table)
            ->where('user_id',1)->where('role_id',2)->first();

        $this->assertTrue(!is_null($user));
        $this->assertInternalType('array',$user);
        $this->assertEquals(5,$user['status']);

        Schema::drop($table);

        $this->assertFalse(Schema::hasTable($table));
    }
}
