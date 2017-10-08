<?php

namespace Tests\Route;

use PHPUnit\Framework\TestCase;
use Spirit\Route;
use Spirit\Request;

class MiddlewareTest extends TestCase
{

    /**
     * @var Route\Routing;
     */
    public static $routing;

    public static function setUpBeforeClass()
    {
        static::$routing = Route::make();

        static::$routing->get('test/user/{user}',[
            'as' => 'test_user',
            'uses' => 'AppController@user',
            'middleware' => 'own'
        ]);
        static::$routing->get('test/admin/{user}',[
            'as' => 'test_admin',
            'uses' => 'AppController@admin',
            'middleware' => 'admin:universe'
        ]);
        static::$routing->get('test/adminGroup/{user}',[
            'as' => 'test_admin_group',
            'uses' => 'AppController@admin',
            'middleware' => 'admin:group'
        ]);
        static::$routing->get('test/is/{user}',[
            'as' => 'test_is',
            'uses' => 'AppController@admin',
            'middleware' => 'is:number_one'
        ]);

        static::$routing->addMiddleware('is',MiddlewareCheck::class);

        static::$routing->addMiddleware('own',function (){
            return false;
        });

        static::$routing->addMiddleware('admin',function ($type){
            return $type === 'universe';
        });
    }

    public function testDefault()
    {
        $result = static::$routing->parse('test/user/1');
        $this->assertNull($result);

        $result = static::$routing->parse('test/adminGroup/1');
        $this->assertNull($result);

        $result = static::$routing->parse('test/admin/1');
        $this->assertNotNull($result);
        $this->assertEquals('test_admin',$result->alias);

        $result = static::$routing->parse('test/is/1');
        $this->assertNotNull($result);
        $this->assertEquals('test_is',$result->alias);
    }

}
