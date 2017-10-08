<?php

namespace Tests\Route;

use PHPUnit\Framework\TestCase;
use Spirit\Route;

class AliasTest extends TestCase
{

    /**
     * @var Route\Routing;
     */
    public static $routing;

    public static function setUpBeforeClass()
    {
        static::$routing = Route::make();

        static::$routing->add('test','AppController@test');
        static::$routing->post('testPost','AppController@testPost');
        static::$routing->get('test/user/{user}',[
            'as' => 'test_user',
            'uses' => 'AppController@testUser'
        ]);
        static::$routing->get('test/book/{book?}',[
            'as' => 'test_book',
            'uses' => 'AppController@testBook'
        ]);
        static::$routing->get('test/int/{int:\d+}',[
            'as' => 'test_int',
            'uses' => 'AppController@testInt'
        ]);
        static::$routing->get('test/str/{str:\D+}',[
            'as' => 'test_str',
            'uses' => 'AppController@testStr'
        ]);
    }

    public function testAliasException()
    {
        $this->expectException(\Exception::class);

        static::$routing->makeUrlForAlias('test_not_found','1');
    }

    public function testParamsException()
    {
        $this->expectException(\Exception::class);

        static::$routing->makeUrlForAlias('test_user');
    }

    public function testDefault()
    {
        $this->assertEquals(static::$routing->makeUrlForAlias('test_book','1'), '/test/book/1');
        $this->assertEquals(static::$routing->makeUrlForAlias('test_book',['book' => 1]), '/test/book/1');
        $this->assertEquals(static::$routing->makeUrlForAlias('test_book',['book' => 1, 'author' => 2]), '/test/book/1?author=2');
        $this->assertEquals(static::$routing->makeUrlForAlias('test_book',['author' => 2]), '/test/book/2');
        $this->assertEquals(static::$routing->makeUrlForAlias('test_book',['book' => 1, 'author' => 2, 'date' => 3]),
        '/test/book/1?author=2&date=3');
    }

}
