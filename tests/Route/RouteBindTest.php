<?php
use PHPUnit\Framework\TestCase;

use Spirit\Structure\Model;
use Spirit\Route;
use Spirit\DB;
use Spirit\DB as db_n;

class TestRouteUserModel extends Model
{
    use \Spirit\Structure\Model\SoftRemoveTrait;
    protected $table = 'test_route_model__users';
    protected $timestamps = true;

}

/**
 * @covers DB
 */
final class RouteBindTest extends TestCase
{

    /**
     * @var Route\Routing;
     */
    public static $routing;

    public static function setUpBeforeClass()
    {
        if (!db_n\Schema::hasTable('test_route_model__users')) {
            db_n\Schema::create('test_route_model__users', function (db_n\schema\Table $table) {
                $table->serial('id')
                    ->timestamps()
                    ->softRemove()
                    ->string('name')
                    ->string('email');
            });
        }

        static::$routing = Route::make();

        static::$routing->add('test', 'AppController@test');
        static::$routing->post('testPost', 'AppController@testPost');
        static::$routing->get('test/user/{user}', [
            'as' => 'test_user',
            'uses' => 'AppController@testUser'
        ]);
        static::$routing->get('test/book/{book?}', [
            'as' => 'test_book',
            'uses' => 'AppController@testBook'
        ]);

        static::$routing->bindModel('user', TestRouteUserModel::class);
        static::$routing->bind('book', function($v) {
            return $v ? $v * 2 : null;
        });
    }

    public static function tearDownAfterClass()
    {
        db_n\Schema::table('test_route_model__users', function(db_n\schema\Table $table) {
            $table->drop();
        });
    }

    public function testModel()
    {
        $user = new TestRouteUserModel(['name' => 'Marat Nuriev', 'email' => 'nurieff@gmail.com']);
        $user->save();

        $result = static::$routing->parse('test/user/25');
        $this->assertNull($result);

        $result = static::$routing->parse('test/user/' . $user->id);
        $this->assertNotNull($result);

        $this->assertArrayHasKey('user', $result->vars);
        $this->assertTrue($result->vars['user'] instanceof TestRouteUserModel);
    }

    public function testCallback()
    {
        $result = static::$routing->parse('test/book');
        $this->assertNotNull($result);
        $this->assertArrayHasKey('book', $result->vars);
        $this->assertNull($result->vars['book']);

        $result = static::$routing->parse('test/book/25');
        $this->assertNotNull($result);
        $this->assertArrayHasKey('book', $result->vars);
        $this->assertEquals(50, $result->vars['book']);
    }
}
