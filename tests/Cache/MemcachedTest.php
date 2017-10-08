<?php

namespace Tests\Cache;

use PHPUnit\Framework\TestCase;
use Spirit\Cache;

/**
 * @covers DB
 */
class MemcachedTest extends TestCase
{
    /**
     * @var Cache\Memcached
     */
    static $cache;

    public static function setUpBeforeClass()
    {
        static::$cache = Cache::store('memcached');
    }

    public function testPut()
    {
        $this->assertInstanceOf(Cache\Memcached::class, static::$cache);

        static::$cache->put('key_string', 'my_value');
        static::$cache->put('key_int', 1);
        static::$cache->put('key_array', [1, 2, 3]);


    }

    /**
     * @depends testPut
     */
    public function testHas()
    {
        $this->assertFalse(static::$cache->has('key_null'));

        $this->assertTrue(static::$cache->has('key_string'));
        $this->assertTrue(static::$cache->has('key_int'));
        $this->assertTrue(static::$cache->has('key_array'));
    }

    /**
     * @depends testHas
     */
    public function testGet()
    {
        $this->assertNull(static::$cache->get('key_null'));

        $this->assertInternalType('string', static::$cache->get('key_string'));
        $this->assertInternalType('int', static::$cache->get('key_int'));
        $this->assertInternalType('array', static::$cache->get('key_array'));

        $this->assertEquals('my_value', static::$cache->get('key_string'));
        $this->assertEquals(1, static::$cache->get('key_int'));
        $this->assertEquals([1, 2, 3], static::$cache->get('key_array'));
    }

    /**
     * @depends testGet
     */
    public function testPull()
    {
        $this->assertEquals('my_value', static::$cache->pull('key_string'));
        $this->assertEquals(1, static::$cache->pull('key_int'));
        $this->assertEquals([1, 2, 3], static::$cache->pull('key_array'));

        $this->assertNull(static::$cache->get('key_string'));
        $this->assertNull(static::$cache->get('key_int'));
        $this->assertNull(static::$cache->get('key_array'));
    }
}
