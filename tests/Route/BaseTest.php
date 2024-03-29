<?php

namespace Tests\Route;

use Tests\TestCase;
use Spirit\Route;
use Spirit\Request;

/**
 * @covers DB
 */
class BaseTest extends TestCase
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

    public function testDefault()
    {
        $result = static::$routing->parse('test');

        $this->assertTrue($result instanceof Route\Current);

        $this->assertEquals('test', $result->methodName);
        $this->assertEquals('AppController', $result->className);
        $this->assertInternalType('array', $result->vars);
    }

//    public function testRouteException()
//    {
//        $this->expectException(\Exception::class);
//
//        Request::imitationMethod('GET');
//
//        static::$routing->parse('testPost');
//    }

    public function testPost()
    {
        Request::imitationMethod('POST');
        $result = static::$routing->parse('testPost');

        $this->assertNotNull($result);

        Request::imitationMethod('GET');
    }

    public function testVarUser()
    {
        $result = static::$routing->parse('test/user');

        $this->assertNull($result);

        $result = static::$routing->parse('test/user/25');

        $this->assertNotNull($result);
        $this->assertTrue($result instanceof Route\Current);
        $this->assertEquals('test_user',$result->alias);
        $this->assertArrayHasKey('user',$result->vars);
    }

    public function testVarInt()
    {
        $result = static::$routing->parse('test/int/asd');

        $this->assertNull($result);

        $result = static::$routing->parse('test/int/25');

        $this->assertNotNull($result);
        $this->assertTrue($result instanceof Route\Current);
        $this->assertEquals('test_int',$result->alias);
        $this->assertArrayHasKey('int',$result->vars);
    }


    public function testVarStr()
    {
        $result = static::$routing->parse('test/str/25');

        $this->assertNull($result);

        $result = static::$routing->parse('test/str/25asd');
        $this->assertNull($result);

        $result = static::$routing->parse('test/str/as24d');
        $this->assertNull($result);

        $result = static::$routing->parse('test/str/as24');
        $this->assertNull($result);

        $result = static::$routing->parse('test/str/asd');

        $this->assertNotNull($result);
        $this->assertTrue($result instanceof Route\Current);
        $this->assertEquals('test_str',$result->alias);
        $this->assertArrayHasKey('str',$result->vars);
    }

    public function testVarBook()
    {
        $result = static::$routing->parse('test/book');

        $this->assertNotNull($result);
        $this->assertTrue($result instanceof Route\Current);

        $result = static::$routing->parse('test/book/25');
        $this->assertNotNull($result);
        $this->assertTrue($result instanceof Route\Current);
        $this->assertEquals('test_book',$result->alias);
        $this->assertArrayHasKey('book',$result->vars);
    }
}
