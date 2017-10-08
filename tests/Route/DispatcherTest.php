<?php

namespace Tests\Route;

use Tests\TestCase;
use Spirit\Response;
use Spirit\Route;
use Spirit\View;
use Spirit\DB;
use Spirit\DB as db_n;

/**
 * @covers DB
 */
final class RouteDispatcherTest extends TestCase
{

    /**
     * @var Route\Routing;
     */
    public static $routing;

    public static function setUpBeforeClass()
    {
        file_put_contents(__DIR__ . '/view.php', '<div><?=$v;?></div>');


        if (!db_n\Schema::hasTable('test_route_dispatcher_model__users')) {
            db_n\Schema::create('test_route_dispatcher_model__users', function(db_n\schema\Table $table) {
                $table->serial('id')
                    ->timestamps()
                    ->softRemove()
                    ->string('name')
                    ->string('email');
            });
        }

        static::$routing = Route::make();

        static::$routing->add('controller/{user}/name', RouteController::class . '@test');
        static::$routing->add('callable/{user}/email', function(DispatcherUserModel $userModel){
            return $userModel->email;
        });
        static::$routing->add('callable/array', function(){
            return ['asd' => 1];
        });
        static::$routing->add('callable/view', function(){
            return View::make(__DIR__ . '/view.php',['v' => 'is_view']);
        });
    }

    public static function tearDownAfterClass()
    {
        db_n\Schema::table('test_route_dispatcher_model__users', function(db_n\schema\Table $table) {
            $table->drop();
        });

        unlink(__DIR__ . '/view.php');
    }

    public function testController()
    {
        $user = new DispatcherUserModel(['name' => 'Marat Nuriev', 'email' => 'nurieff@gmail.com']);
        $user->save();

        $result = static::$routing->parse('controller/25/name');
        $this->assertNotNull($result);

        $response = Route\Dispatcher::make($result)->response();
        $this->assertNull($response);

        $result = static::$routing->parse('controller/' . $user->id . '/name');
        $this->assertNotNull($result);

        $response = Route\Dispatcher::make($result)->response();

        $this->assertNotNull($response);
        $this->assertTrue($response instanceof Response);
        $this->assertEquals('Marat Nuriev',$response);
    }

    public function testCallback()
    {
        $user = new DispatcherUserModel(['name' => 'Marat Nuriev', 'email' => 'nurieff@gmail.com']);
        $user->save();

        $result = static::$routing->parse('callable/25/email');
        $this->assertNotNull($result);

        $response = Route\Dispatcher::make($result)->response();
        $this->assertNull($response);

        $result = static::$routing->parse('callable/' . $user->id . '/email');
        $this->assertNotNull($result);

        $response = Route\Dispatcher::make($result)->response();
        $this->assertNotNull($response);
        $this->assertEquals('nurieff@gmail.com',$response);
    }

    public function testResponseArray()
    {
        $result = static::$routing->parse('callable/array');
        $this->assertNotNull($result);
        $response = Route\Dispatcher::make($result)->response();
        $this->assertNotNull($response);
        $this->assertEquals('{"asd":1}',$response->toString());
    }

    public function testView()
    {
        $result = static::$routing->parse('callable/view');
        $this->assertNotNull($result);

        $response = Route\Dispatcher::make($result)->response();
        $this->assertTrue($response instanceof Response);
        $this->assertEquals('<div>is_view</div>', $response->toString());
    }
}
