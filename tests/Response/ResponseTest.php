<?php

namespace Tests\Response;

use PHPUnit\Framework\TestCase;
use Spirit\Response;

class TestResponseDataArr implements \Spirit\Structure\Arrayable {

    protected $data = [];

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function toArray()
    {
        return $this->data;
    }
}

class TestResponseDataJson implements \Spirit\Structure\Jsonable {

    protected $data = [];

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function toJson($options = 0)
    {
        return json_encode($this->data, $options);
    }
}

class ResponseTest extends TestCase
{
    public function testMake()
    {
        $r = Response::make();

        $this->assertTrue($r instanceof Response);
    }

    public function testString()
    {
        $r = new Response('Marat');

        $this->assertEquals('Marat',(string)$r);
    }

    public function testArray()
    {
        $r = new Response(['Marat','Nuriev']);

        $this->assertEquals('["Marat","Nuriev"]',(string)$r);
    }

    public function testArrayable()
    {
        $data = new TestResponseDataArr(['Marat','Nuriev']);

        $r = new Response($data);

        $this->assertEquals('["Marat","Nuriev"]',(string)$r);
    }

    public function testJsonable()
    {
        $data = new TestResponseDataJson(['Marat','Nuriev']);

        $r = new Response($data);

        $this->assertEquals('["Marat","Nuriev"]',(string)$r);
    }

}
