<?php

namespace Tests\Model;

use Tests\TestCase;
use Spirit\Structure\Model;
use Spirit\DB;
use Spirit\DB as db_n;

function __getTableName()
{
    return 'test_base_model__users';
}

/**
 * @covers DB
 */
class BaseTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        if (!db_n\Schema::hasTable(__getTableName())) {
            db_n\Schema::create(__getTableName(), function(db_n\schema\Table $table) {
                $table->serial('id')
                    ->timestamps()
                    ->softRemove()
                    ->string('name')
                    ->string('email');
            });
        }
    }

    public static function tearDownAfterClass()
    {
        db_n\Schema::table(__getTableName(), function(db_n\schema\Table $table) {
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
        $user = new UserModel(['name' => 'Marat Nuriev', 'email' => 'nurieff@gmail.com']);

        $this->assertTrue($user instanceof Model);
        $this->assertTrue($user instanceof UserModel);

        return $user;
    }

    /**
     * @param UserModel $user
     * @return UserModel
     * @depends testCreate
     */
    public function testSave(UserModel $user)
    {
        $user->save();

        $this->assertNotEquals(null, $user->id);
        $this->assertInternalType('integer', $user->id);

        $amount = DB::table(__getTableName())
            ->count();

        $this->assertEquals(1, $amount);

        return $user;
    }

    /**
     * @param UserModel $user
     * @return UserModel
     * @depends testSave
     */
    public function testSoftRemove(UserModel $user)
    {
        $user->remove();

        $amount = DB::table(__getTableName())
            ->count();
        $this->assertEquals(1, $amount);

        $amount = DB::table(__getTableName())
            ->whereNotNull('removed_at')
            ->count();
        $this->assertEquals(1, $amount);

        $amount = DB::table(__getTableName())
            ->whereNull('removed_at')
            ->count();
        $this->assertEquals(0, $amount);

        return $user;
    }

    /**
     * @param UserModel $user
     * @return UserModel
     * @depends testSoftRemove
     */
    public function testRestore(UserModel $user)
    {
        $user->restore();

        $amount = DB::table(__getTableName())
            ->whereNotNull('removed_at')
            ->count();
        $this->assertEquals(0, $amount);

        $amount = DB::table(__getTableName())
            ->whereNull('removed_at')
            ->count();
        $this->assertEquals(1, $amount);

        return $user->reload();
    }

    /**
     * @param UserModel $user
     * @return UserModel
     * @depends testRestore
     */
    public function testEdit(UserModel $user)
    {
        $this->assertEquals('Marat Nuriev', $user->name);

        $user->name = 'Nuriev Marat';
        $user->save();

        $freshUser = UserModel::find($user->id);

        $this->assertEquals('Nuriev Marat', $freshUser->name);

        $freshUser->name = 'Marat Nuriev';
        $freshUser->save();

        $this->assertEquals('Marat Nuriev', $freshUser->name);

        return $freshUser;
    }

    /**
     * @param UserModel $user
     * @depends testEdit
     */
    public function testRemove(UserModel $user)
    {
        $user->forceRemove();

        $amount = DB::table(__getTableName())
            ->count();
        $this->assertEquals(0, $amount);
    }

    /**
     * @depends testRemove
     */
    public function testHelper()
    {
        $user = new UserSecondModel(['name' => 'Marat Nuriev', 'email' => 'nurieff@gmail.com']);
        $user->save();

        $this->assertTrue($user instanceof Model);
        $this->assertTrue($user instanceof UserSecondModel);

        $this->assertNull($user->email);
        $this->assertEquals('is_test_param',$user->test_param);

        $a = $user->toArray();

        $this->assertArrayHasKey('id',$a);
        $this->assertArrayHasKey('name',$a);
        $this->assertArrayHasKey('test_param',$a);
        $this->assertArrayNotHasKey('email',$a);

        $user->name = 'There is not Marat Nuriev';
        $user->where('name','asd')->save();

        $fresh_user = $user->reload();
        $this->assertEquals('Marat Nuriev',$fresh_user->name);


        return $user;
    }

}
