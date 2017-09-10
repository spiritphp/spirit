<?php

use PHPUnit\Framework\TestCase;

use Spirit\Request;

final class RequestTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        $post = [
            'name' => 'Marat',
            'surname' => 'Nuriev',
            'type' => 1
        ];

        $get = [
            'type' => 2,
            'age' => 30
        ];

        $server = [
            'TEST' => 'This is test server_var',
            'HTTP_TEST' => 'This is test header'
        ];

        $files = [
            'image' => [
                'name' => 'photo.jpg',
                'size' => '60000',
                'type' => 'iamge/jpeg',
                'tmp_name' => '/tmp/blabla',
                'error' => UPLOAD_ERR_NO_FILE,
            ]
        ];

        Request::init(
            $post,
            $get,
            $server,
            $files
        );

        Request::imitationMethod('POST');
    }

    public static function tearDownAfterClass()
    {
        Request::clean();
    }

    public function testDefault()
    {
        $this->assertTrue(Request::isPOST());
        $this->assertTrue(Request::isMethod(Request::METHOD_POST));
        $this->assertFalse(Request::isAjax());
    }

    public function testGet()
    {
        $this->assertEquals('Marat', Request::get('name'));
        $this->assertEquals('Nuriev', Request::get('surname'));
        $this->assertEquals(1, Request::get('type'));
        $this->assertNull(Request::get('age'));
    }

    public function testPost()
    {
        $this->assertEquals('Marat', Request::post('name'));
        $this->assertEquals('Nuriev', Request::post('surname'));
        $this->assertEquals(1, Request::post('type'));
        $this->assertNull(Request::post('age'));
    }

    public function testQuery()
    {
        $this->assertNull(Request::query('name'));
        $this->assertNull(Request::query('surname'));
        $this->assertEquals(1, Request::post('type'));
    }

    public function testFile()
    {
        $f = Request::file('image');
        $this->assertNotNull($f);
        $this->assertTrue($f instanceof Request\UploadedFile);

        $this->assertNull(Request::file('photo'));
    }

    public function testHeader()
    {
        $h = Request::header('TEST');
        $this->assertNotNull($h);

        $this->assertEquals('This is test header', $h);
    }

    public function testServer()
    {
        $h = Request::server('TEST');
        $this->assertNotNull($h);

        $this->assertEquals('This is test server_var', $h);
    }

    public function testVariables()
    {
        $this->assertTrue(Request::get() instanceof Request\Variables);
        $this->assertTrue(Request::query() instanceof Request\Variables);
        $this->assertTrue(Request::post() instanceof Request\Variables);
        $this->assertTrue(Request::header() instanceof Request\Variables);
        $this->assertTrue(Request::header() instanceof Request\HeaderVariables);
        $this->assertTrue(Request::server() instanceof Request\Variables);
        $this->assertTrue(Request::file() instanceof Request\FileVariables);
    }

    public function testModify()
    {
        $vars = Request::get()->all();
        $this->assertCount(3, $vars);
        $this->assertEquals([
            'name' => 'Marat',
            'surname' => 'Nuriev',
            'type' => 1
        ], $vars);

        $vars = Request::get()->only('name', 'type');
        $this->assertCount(2, $vars);
        $this->assertEquals([
            'name' => 'Marat',
            'type' => 1
        ], $vars);

        $vars = Request::get()->except(['name', 'type']);
        $this->assertCount(1, $vars);
        $this->assertEquals([
            'surname' => 'Nuriev'
        ], $vars);

        $this->assertTrue(Request::get()->has('name', 'type'));
        $this->assertTrue(Request::get()->has(['name', 'type']));
        $this->assertFalse(Request::get()->has('name', 'type', 'lose'));
        $this->assertFalse(Request::get()->has('lose'));
    }
}
