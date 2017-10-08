<?php

namespace Tests\Dotenv;

use PHPUnit\Framework\TestCase;
use Spirit\Config\Dotenv;

/**
 * @covers DB
 */
final class DotenvTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        $d = [
            "V_TRUE=true",
            "V_ARRAY=[\"asd\",\"qwe\",\"zxc\"]",
            "V_ARRAY_2={\"key\":\"value\"}",
            "V_ARRAY_3=[asd, 123, asd]",
            "V_NO_MOD=\"{\"key\":\"value\"}\"",
            "V_NO_MOD_2='{\"key\":\"value\"}'",
            "V_NUMBER=5",
            "V_FLOAT=5.1",
            "V_STRING=String",
            "#V_COMMENT=String",
            "V_STRING=String2"
        ];

        file_put_contents(__DIR__ . '/dotenvtest.env', implode("\n",$d));
    }

    public static function tearDownAfterClass()
    {
        unlink(__DIR__ . '/dotenvtest.env');
    }

    public function testEnv()
    {
        $de = Dotenv::make(__DIR__ . '/dotenvtest.env');

        $this->assertTrue($de->getEnvValue('V_TRUE'));
        $this->assertNull($de->getEnvValue('V_COMMENT'));
        $this->assertEquals('String2',$de->getEnvValue('V_STRING'));
        $this->assertInternalType('array',$de->getEnvValue('V_ARRAY'));
        $this->assertInternalType('array',$de->getEnvValue('V_ARRAY_2'));
        $this->assertInternalType('array',$de->getEnvValue('V_ARRAY_3'));
        $this->assertArrayHasKey('key',$de->getEnvValue('V_ARRAY_2'));
        $this->assertEquals(5,$de->getEnvValue('V_NUMBER'));
        $this->assertEquals(5.1,$de->getEnvValue('V_FLOAT'));
        $this->assertEquals("{\"key\":\"value\"}",$de->getEnvValue('V_NO_MOD'));
        $this->assertEquals("{\"key\":\"value\"}",$de->getEnvValue('V_NO_MOD_2'));
    }

    public function testSave()
    {
        $de = Dotenv::make(__DIR__ . '/dotenvtest.env');

        $de->set('NEW_KEY','NEW_VALUE');



        $de->save(__DIR__ . '/newdotenvtest.env');

        $nde = Dotenv::make(__DIR__ . '/newdotenvtest.env');
        $this->assertTrue($nde->getEnvValue('V_TRUE'));
        $this->assertNull($nde->getEnvValue('V_COMMENT'));
        $this->assertEquals('NEW_VALUE',$nde->getEnvValue('NEW_KEY'));
        $this->assertInternalType('array',$nde->getEnvValue('V_ARRAY'));
        $this->assertInternalType('array',$nde->getEnvValue('V_ARRAY_2'));
        $this->assertInternalType('array',$nde->getEnvValue('V_ARRAY_3'));
        $this->assertArrayHasKey('key',$nde->getEnvValue('V_ARRAY_2'));
        $this->assertEquals("{\"key\":\"value\"}",$nde->getEnvValue('V_NO_MOD'));
        $this->assertEquals("{\"key\":\"value\"}",$nde->getEnvValue('V_NO_MOD_2'));

        unlink(__DIR__ . '/newdotenvtest.env');
    }

}
