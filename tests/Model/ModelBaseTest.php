<?php
use PHPUnit\Framework\TestCase;
use Spirit\Structure\Model;
use Spirit\DB;
use Spirit\DB as db_n;

class TestUserModel extends Model
{
    use \Spirit\Structure\Model\SoftRemoveTrait;
    protected $table = 'test_base_model__users';
    protected $timestamps = true;

}


class TestUserSecondModel extends Model
{
    use \Spirit\Structure\Model\SoftRemoveTrait;
    protected $table = 'test_base_model__users';
    protected $timestamps = true;

    protected $hidden = ['email','updated_at','created_at','removed_at'];
    protected $visible = ['test_param'];

    public function getTestParamData()
    {
        return 'is_test_param';
    }

}

function __getTableName()
{
    return 'test_base_model__users';
}

/**
 * @covers DB
 */
final class ModelBaseTest extends TestCase
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
        $user = new TestUserModel(['name' => 'Marat Nuriev', 'email' => 'nurieff@gmail.com']);

        $this->assertTrue($user instanceof Model);
        $this->assertTrue($user instanceof TestUserModel);

        return $user;
    }

    /**
     * @param TestUserModel $user
     * @return TestUserModel
     * @depends testCreate
     */
    public function testSave(TestUserModel $user)
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
     * @param TestUserModel $user
     * @return TestUserModel
     * @depends testSave
     */
    public function testSoftRemove(TestUserModel $user)
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
     * @param TestUserModel $user
     * @return TestUserModel
     * @depends testSoftRemove
     */
    public function testRestore(TestUserModel $user)
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
     * @param TestUserModel $user
     * @return TestUserModel
     * @depends testRestore
     */
    public function testEdit(TestUserModel $user)
    {
        $this->assertEquals('Marat Nuriev', $user->name);

        $user->name = 'Nuriev Marat';
        $user->save();

        $freshUser = TestUserModel::find($user->id);

        $this->assertEquals('Nuriev Marat', $freshUser->name);

        $freshUser->name = 'Marat Nuriev';
        $freshUser->save();

        $this->assertEquals('Marat Nuriev', $freshUser->name);

        return $freshUser;
    }

    /**
     * @param TestUserModel $user
     * @depends testEdit
     */
    public function testRemove(TestUserModel $user)
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
        $user = new TestUserSecondModel(['name' => 'Marat Nuriev', 'email' => 'nurieff@gmail.com']);
        $user->save();

        $this->assertTrue($user instanceof Model);
        $this->assertTrue($user instanceof TestUserSecondModel);

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
