<?php

namespace Tests\DataBase;

use Tests\TestCase;
use Spirit\DB;
use Spirit\DB as db_n;

/**
 * @covers DB
 */
class QueryTest extends TestCase
{

    protected static $__tableName = 'spirit_test__users';

    public static function setUpBeforeClass()
    {
        if (!db_n\Schema::hasTable(static::$__tableName)) {
            db_n\Schema::create(static::$__tableName, function (db_n\schema\Table $table) {
                $table->serial('id')
                    ->string('name')
                    ->string('email')
                    ->json('data')
                    ->boolean('is_admin')->default(false);
            });
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
        $sql = "
            INSERT INTO
              " . static::$__tableName . " (name,email,data,is_admin)
            VALUES (?, ?, ?, ?),(?, ?, ?, ?) 
        ";

        DB::execute($sql, [
            'Nuriev Marat',
            "nurieff@gmail.com",
            null,
            false,
            'Nuriev Marsele',
            "nurieff@gmail.com",
            '{"b":1,"s":[1,2,3]}',
            true
        ]);
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

    public function testConnect()
    {
        $this->assertTrue(DB::connect() instanceof db_n\Connection);
    }

    public function testConnectInstance()
    {
        $a = DB::connect();
        $b = DB::connect();
        $c = DB::connect();

        $this->assertCount(1, DB::getConnections());
    }

    public function testConnection()
    {
        $this->assertTrue(DB::connect()->getConnection() instanceof \PDO);
    }

    public function testQuery()
    {
        $users = DB::query("SELECT * FROM " . static::$__tableName . " WHERE id = 1 LIMIT 1");

        $this->assertTrue($users instanceof \PDOStatement);
    }

    public function testSelect()
    {
        $users = DB::select("SELECT * FROM " . static::$__tableName . " WHERE id > 0");

        $this->assertInternalType('array',$users);
        $this->assertCount(2,$users);
    }

    public function testBoolean()
    {
        $users = DB::select("SELECT * FROM " . static::$__tableName . " WHERE is_admin = true");
        $userTrue = $users[0];

        $users = DB::select("SELECT * FROM " . static::$__tableName . " WHERE is_admin = false");
        $userFalse = $users[0];

        if (DB::isDriver(DB::DRIVER_POSTGRESQL)) {
            $this->assertTrue($userTrue['is_admin']);
            $this->assertFalse($userFalse['is_admin']);
        } else {
            $this->assertTrue($userTrue['is_admin'] === 1);
            $this->assertTrue($userFalse['is_admin'] === 0);
        }


    }

    public function testJSON()
    {
        $users = DB::select("SELECT * FROM " . static::$__tableName . " WHERE is_admin = true");
        $user = $users[0];
        $this->assertJson($user['data']);
        $this->assertInternalType('array',json_decode($user['data'],1));

        $users = DB::select("SELECT * FROM " . static::$__tableName . " WHERE is_admin = false");
        $user = $users[0];
        $this->assertNull($user['data']);
    }
}
