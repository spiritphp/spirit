<?php

namespace Tests\Validator;

use Tests\TestCase;
use Spirit\Services\Validator;
use Spirit\DB;
use Spirit\DB as db_n;

/**
 * @covers DB
 */
class RelationTest extends TestCase
{

    public function testCheckImage()
    {

        $r = [
            'v' => 'image'
        ];

        $this->assertTrue(Validator::make([
            'v' => \Spirit\FileSystem\File::make(__DIR__ . '/files/oil.png')
        ], $r)->check());

    }

    public function testMin()
    {
        $r = [
            'v' => 'min:2'
        ];

        $this->assertTrue(Validator::make([
            'v' => \Spirit\FileSystem\File::make(__DIR__ . '/files/coin.png')
        ], $r)->check());

        $this->assertFalse(Validator::make([
            'v' => \Spirit\FileSystem\File::make(__DIR__ . '/files/oil.png')
        ], $r)->check());

    }

    public function testMax()
    {
        $r = [
            'v' => 'max:2'
        ];

        $this->assertFalse(Validator::make([
            'v' => \Spirit\FileSystem\File::make(__DIR__ . '/files/coin.png')
        ], $r)->check());

        $this->assertTrue(Validator::make([
            'v' => \Spirit\FileSystem\File::make(__DIR__ . '/files/oil.png')
        ], $r)->check());

    }

    public function testBetween()
    {
        $r = [
            'v' => 'between:1,2'
        ];

        $this->assertFalse(Validator::make([
            'v' => \Spirit\FileSystem\File::make(__DIR__ . '/files/coin.png')
        ], $r)->check());

        $this->assertTrue(Validator::make([
            'v' => \Spirit\FileSystem\File::make(__DIR__ . '/files/oil.png')
        ], $r)->check());

    }

    public function testExists()
    {
        if (!db_n\Schema::hasTable('test_validator__exists')) {
            db_n\Schema::create('test_validator__exists',function(db_n\schema\Table $table) {
                $table->serial('id')
                    ->string('name')
                    ->string('email')
                    ->boolean('is_admin')->default(false);
            });

            $sql = "
                INSERT INTO
                  test_validator__exists (name,email,is_admin)
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

        $r = [
            'email' => 'exists:test_validator__exists'
        ];

        $this->assertTrue(Validator::make([
            'email' => "nurieff@gmail.com"
        ], $r)->check());

        $this->assertFalse(Validator::make([
            'email' => "nenurieff@gmail.com"
        ], $r)->check());


        $r = [
            'k' => 'exists:test_validator__exists,email'
        ];

        $this->assertTrue(Validator::make([
            'k' => "nurieff@gmail.com"
        ], $r)->check());

        $this->assertFalse(Validator::make([
            'k' => "nenurieff@gmail.com"
        ], $r)->check());



        $r = [
            'k' => 'exists:test_validator__exists,email,is_admin,true'
        ];

        $this->assertTrue(Validator::make([
            'k' => "nurieff@gmail.com"
        ], $r)->check());

        $this->assertFalse(Validator::make([
            'k' => "nenurieff@gmail.com"
        ], $r)->check());


        //BUILDER

        $r = [
            'email' => [
                [
                    'exists',
                    DB::table('test_validator__exists')
                ]
            ]
        ];

        $this->assertTrue(Validator::make([
            'email' => "nurieff@gmail.com"
        ], $r)->check());

        $r = [
            'k' => [
                [
                    'exists',
                    DB::table('test_validator__exists'),
                    'email'
                ]
            ]
        ];

        $this->assertTrue(Validator::make([
            'k' => "nurieff@gmail.com"
        ], $r)->check());

        db_n\Schema::table('test_validator__exists', function (db_n\schema\Table $table) {
            $table->drop();
        });
    }

    public function testUnique()
    {
        if (!db_n\Schema::hasTable('test_validator__unique')) {
            db_n\Schema::create('test_validator__unique',function(db_n\schema\Table $table) {
                $table->serial('id')
                    ->string('name')
                    ->string('email')
                    ->boolean('is_admin')->default(false);
            });

            $sql = "
                INSERT INTO
                  test_validator__unique (name,email,is_admin)
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

        $r = [
            'email' => 'unique:test_validator__unique'
        ];

        $this->assertFalse(Validator::make([
            'email' => "nurieff@gmail.com"
        ], $r)->check());

        $this->assertTrue(Validator::make([
            'email' => "nenurieff@gmail.com"
        ], $r)->check());

        $r = [
            'k' => 'unique:test_validator__unique,email'
        ];

        $this->assertFalse(Validator::make([
            'k' => "nurieff@gmail.com"
        ], $r)->check());

        $this->assertTrue(Validator::make([
            'k' => "nenurieff@gmail.com"
        ], $r)->check());


        $r = [
            'k' => 'unique:test_validator__unique,email,is_admin,true'
        ];

        $this->assertFalse(Validator::make([
            'k' => "nurieff@gmail.com"
        ], $r)->check());

        $this->assertTrue(Validator::make([
            'k' => "nenurieff@gmail.com"
        ], $r)->check());

        //BUILDER

        $r = [
            'email' => [
                [
                    'unique',
                    DB::table('test_validator__unique')
                ]
            ]
        ];

        $this->assertFalse(Validator::make([
            'email' => "nurieff@gmail.com"
        ], $r)->check());

        $r = [
            'k' => [
                [
                    'unique',
                    DB::table('test_validator__unique'),
                    'email'
                ]
            ]
        ];

        $this->assertFalse(Validator::make([
            'k' => "nurieff@gmail.com"
        ], $r)->check());


        db_n\Schema::table('test_validator__unique', function (db_n\schema\Table $table) {
            $table->drop();
        });
    }
}