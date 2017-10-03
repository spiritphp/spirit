<?php
use PHPUnit\Framework\TestCase;

use \Spirit\Services\Validator;

/**
 * @covers DB
 */
final class ValidatorTest extends TestCase
{

    public function testBase()
    {
        $v = Validator::make([
            'email' => 'asd',
            'is_num' => 5
        ], [
            'email' => 'email',
            'is_num' => 'numeric'
        ]);

        $this->assertFalse($v->check());
        $this->assertArrayHasKey('email', $v->errors());
        $this->assertArrayNotHasKey('is_num', $v->errors());
        $this->assertInternalType('string', $v->errors()->first('email'));

        $v = Validator::make([
            'email' => 'asd@asd.ru'
        ], [
            'email' => 'email'
        ]);
        $this->assertTrue($v->check());

        $v = Validator::make([
            'email' => 'asd@asd.ru'
        ], [
            'email' => 'required|string|email'
        ]);
        $this->assertTrue($v->check());

        $v = Validator::make([
            'email' => 5
        ], [
            'email' => [
                'required','string','email'
            ]
        ]);
        $this->assertFalse($v->check());

        $this->assertTrue(Validator::make('asd@asd.ru','email')->check());
        $this->assertFalse(Validator::make('assd.ru','email')->check());
    }

    public function testRequired()
    {
        $d = [];
        $d2 = [
            'key' => 'asd',
        ];
        $r = [
            'key' => 'required'
        ];

        $this->assertFalse(Validator::make($d, $r)->check());

        $this->assertTrue(Validator::make($d2, $r)->check());
    }

    public function testRequiredIf()
    {
        $d = [
            'name' => 'asd',
        ];
        $r = [
            'name' => 'required_if:nick'
        ];

        $this->assertTrue(Validator::make($d, $r)->check());

        $d = [
            'nick' => 'My nick'
        ];
        $r = [
            'name' => 'required_if:nick'
        ];
        $this->assertFalse(Validator::make($d, $r)->check());

        $d = [
            'name' => 'My name',
            'nick' => 'My nick'
        ];
        $r = [
            'name' => 'required_if:nick'
        ];
        $this->assertTrue(Validator::make($d, $r)->check());

        $d = [
            'nick' => 'My nick'
        ];
        $r = [
            'name' => 'required_if:nick,nurieff'
        ];
        $this->assertTrue(Validator::make($d, $r)->check());

        $d = [
            'nick' => 'nurieff'
        ];
        $r = [
            'name' => 'required_if:nick,nurieff'
        ];
        $this->assertFalse(Validator::make($d, $r)->check());

        $d = [
            'name' => 'My name',
            'nick' => 'nurieff'
        ];
        $r = [
            'name' => 'required_if:nick,nurieff'
        ];
        $this->assertTrue(Validator::make($d, $r)->check());
    }

    public function testEmail()
    {
        $d = [
            'email' => 'asd@asd.ru',
        ];
        $d2 = [
            'email' => 'asd',
        ];
        $d3 = [
            'email' => 'asd@asd',
        ];
        $r = [
            'email' => 'email'
        ];

        $this->assertTrue(Validator::make($d, $r)->check());
        $this->assertFalse(Validator::make($d2, $r)->check());
        $this->assertFalse(Validator::make($d3, $r)->check());
    }

    public function testSame()
    {
        $d = [
            'key' => 'Qwerty',
            'key2' => 'Qwerty'
        ];
        $d2 = [
            'key' => 'Qwerty2',
            'key2' => 'Qwerty'
        ];
        $r = [
            'key' => 'same:key2'
        ];

        $this->assertTrue(Validator::make($d, $r)->check());
        $this->assertFalse(Validator::make($d2, $r)->check());
    }

    public function testConfirmed()
    {
        $d = [
            'key' => 'Qwerty',
            'key_confirmation' => 'Qwerty'
        ];
        $d2 = [
            'key' => 'Qwerty2'
        ];
        $r = [
            'key' => 'confirmed'
        ];

        $this->assertTrue(Validator::make($d, $r)->check());
        $this->assertFalse(Validator::make($d2, $r)->check());
    }

    public function testUrl()
    {
        $d = [
            'url' => 'https://github.com'
        ];
        $d2 = [
            'url' => 'https://github.com/asd'
        ];
        $d3 = [
            'url' => 'http://localhost'
        ];
        $d4 = [
            'url' => 'https://www.github.com'
        ];
        $d5 = [
            'url' => 'https://www.home.github.com'
        ];
        $d6 = [
            'url' => 'httpsm'
        ];
        $r = [
            'url' => 'url'
        ];

        $this->assertTrue(Validator::make($d, $r)->check());
        $this->assertTrue(Validator::make($d2, $r)->check());
        $this->assertTrue(Validator::make($d3, $r)->check());
        $this->assertTrue(Validator::make($d4, $r)->check());
        $this->assertTrue(Validator::make($d5, $r)->check());
        $this->assertFalse(Validator::make($d6, $r)->check());
    }

    public function testInteger()
    {
        $r = [
            'v' => 'integer'
        ];
        $this->assertTrue(Validator::make([
            'v' => 1
        ], $r)->check());

        $this->assertFalse(Validator::make([
            'v' => 1.1
        ], $r)->check());

        $this->assertFalse(Validator::make([
            'v' => '1.1'
        ], $r)->check());

        $this->assertFalse(Validator::make([
            'v' => '1'
        ], $r)->check());

        $this->assertFalse(Validator::make([
            'v' => '0'
        ], $r)->check());

        $this->assertTrue(Validator::make([
            'v' => 0
        ], $r)->check());

        $this->assertFalse(Validator::make([
            'v' => '00005'
        ], $r)->check());

        $this->assertFalse(Validator::make([
            'v' => 'asd'
        ], $r)->check());

    }

    public function testString()
    {
        $r = [
            'v' => 'string'
        ];
        $this->assertFalse(Validator::make([
            'v' => 1
        ], $r)->check());

        $this->assertFalse(Validator::make([
            'v' => 1.1
        ], $r)->check());

        $this->assertTrue(Validator::make([
            'v' => '1.1'
        ], $r)->check());

        $this->assertTrue(Validator::make([
            'v' => '1'
        ], $r)->check());

        $this->assertTrue(Validator::make([
            'v' => '0'
        ], $r)->check());

        $this->assertFalse(Validator::make([
            'v' => 0
        ], $r)->check());

        $this->assertTrue(Validator::make([
            'v' => '00005'
        ], $r)->check());

        $this->assertTrue(Validator::make([
            'v' => 'asd'
        ], $r)->check());

    }

    public function testNumeric()
    {
        $r = [
            'v' => 'numeric'
        ];
        $this->assertTrue(Validator::make([
            'v' => 1
        ], $r)->check());

        $this->assertTrue(Validator::make([
            'v' => 1.1
        ], $r)->check());

        $this->assertTrue(Validator::make([
            'v' => '1.1'
        ], $r)->check());

        $this->assertTrue(Validator::make([
            'v' => '1'
        ], $r)->check());

        $this->assertTrue(Validator::make([
            'v' => '0'
        ], $r)->check());

        $this->assertTrue(Validator::make([
            'v' => 0
        ], $r)->check());

        $this->assertTrue(Validator::make([
            'v' => '00005'
        ], $r)->check());

        $this->assertFalse(Validator::make([
            'v' => 'asd'
        ], $r)->check());

    }

    public function testDate()
    {
        $r = [
            'v' => 'date'
        ];
        $this->assertTrue(Validator::make([
            'v' => '2017-01-01'
        ], $r)->check());

        $this->assertFalse(Validator::make([
            'v' => '2017-13-01'
        ], $r)->check());

        $this->assertTrue(Validator::make([
            'v' => '01.02.2017'
        ], $r)->check());

        $this->assertFalse(Validator::make([
            'v' => '30.13.2017'
        ], $r)->check());

    }

    public function testDateFormat()
    {
        $r = [
            'v' => 'date_format:Y-m-d'
        ];
        $this->assertTrue(Validator::make([
            'v' => '2017-01-01'
        ], $r)->check());

        $this->assertFalse(Validator::make([
            'v' => '2017-13-01'
        ], $r)->check());

        $this->assertFalse(Validator::make([
            'v' => '01.02.2017'
        ], $r)->check());

    }

    public function testRegex()
    {
        $r = [
            'v' => 'regex:/^\d{2,3}$/ius'
        ];
        $this->assertTrue(Validator::make([
            'v' => '20'
        ], $r)->check());

        $this->assertTrue(Validator::make([
            'v' => '200'
        ], $r)->check());

        $this->assertFalse(Validator::make([
            'v' => 'e20'
        ], $r)->check());

        $this->assertFalse(Validator::make([
            'v' => '4000'
        ], $r)->check());

        $this->assertFalse(Validator::make([
            'v' => 'sad'
        ], $r)->check());

    }

    public function testMin()
    {
        $r = [
            'v' => 'min:2'
        ];

        $this->assertTrue(Validator::make([
            'v' => 3
        ], $r)->check());

        $this->assertFalse(Validator::make([
            'v' => 1
        ], $r)->check());

        $this->assertTrue(Validator::make([
            'v' => 'as'
        ], $r)->check());

        $this->assertFalse(Validator::make([
            'v' => 'a'
        ], $r)->check());

        $this->assertTrue(Validator::make([
            'v' => [1,2]
        ], $r)->check());

        $this->assertFalse(Validator::make([
            'v' => [1]
        ], $r)->check());

    }

    public function testMax()
    {
        $r = [
            'v' => 'max:2'
        ];

        $this->assertTrue(Validator::make([
            'v' => 2
        ], $r)->check());

        $this->assertFalse(Validator::make([
            'v' => 3
        ], $r)->check());

        $this->assertTrue(Validator::make([
            'v' => 'as'
        ], $r)->check());

        $this->assertFalse(Validator::make([
            'v' => 'asd'
        ], $r)->check());

        $this->assertTrue(Validator::make([
            'v' => [1,2]
        ], $r)->check());

        $this->assertFalse(Validator::make([
            'v' => [1,2,3]
        ], $r)->check());

    }

    public function testBetween()
    {
        $r = [
            'v' => 'between:2,3'
        ];

        $this->assertTrue(Validator::make([
            'v' => 2
        ], $r)->check());

        $this->assertTrue(Validator::make([
            'v' => 3
        ], $r)->check());

        $this->assertFalse(Validator::make([
            'v' => 4
        ], $r)->check());

        $this->assertFalse(Validator::make([
            'v' => 1
        ], $r)->check());

        $this->assertTrue(Validator::make([
            'v' => 'as'
        ], $r)->check());

        $this->assertTrue(Validator::make([
            'v' => 'asd'
        ], $r)->check());

        $this->assertFalse(Validator::make([
            'v' => 'asdf'
        ], $r)->check());

        $this->assertFalse(Validator::make([
            'v' => 'a'
        ], $r)->check());

        $this->assertTrue(Validator::make([
            'v' => [1,2]
        ], $r)->check());

        $this->assertTrue(Validator::make([
            'v' => [1,2,3]
        ], $r)->check());

        $this->assertFalse(Validator::make([
            'v' => [1,2,3,4]
        ], $r)->check());

        $this->assertFalse(Validator::make([
            'v' => [1]
        ], $r)->check());

    }

    public function testBoolean()
    {
        $r = [
            'v' => 'boolean'
        ];

        $this->assertTrue(Validator::make([
            'v' => 1
        ], $r)->check());

        $this->assertTrue(Validator::make([
            'v' => '1'
        ], $r)->check());

        $this->assertTrue(Validator::make([
            'v' => true
        ], $r)->check());

        $this->assertTrue(Validator::make([
            'v' => 0
        ], $r)->check());

        $this->assertTrue(Validator::make([
            'v' => '0'
        ], $r)->check());

        $this->assertTrue(Validator::make([
            'v' => false
        ], $r)->check());

        $this->assertFalse(Validator::make([
            'v' => 'asd'
        ], $r)->check());

        $this->assertFalse(Validator::make([
            'v' => 'false'
        ], $r)->check());
    }

    public function testBefore()
    {
        $r = [
            'v' => 'before:2017-01-07'
        ];

        $this->assertTrue(Validator::make([
            'v' => '2017-01-06'
        ], $r)->check());

        $this->assertFalse(Validator::make([
            'v' => '2017-01-08'
        ], $r)->check());

    }

    public function testAfter()
    {
        $r = [
            'v' => 'after:2017-01-07'
        ];

        $this->assertTrue(Validator::make([
            'v' => '2017-01-08'
        ], $r)->check());

        $this->assertFalse(Validator::make([
            'v' => '2017-01-06'
        ], $r)->check());

    }
}
