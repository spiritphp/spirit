<?php
use PHPUnit\Framework\TestCase;

use \Spirit\Response\Captcha;

/**
 * @covers DB
 */
final class ServicesCaptchaTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        \Spirit\Response\Session::initTest();
    }

    public function testUid()
    {
        $uid = Captcha::make()->getUniqueId();

        $this->assertNotNull($uid);

        $string = Captcha::make()->uniqueId($uid)->draw();

        $this->assertInternalType('string', $string);

        $this->assertTrue(Captcha::check($uid,$string));

        $this->assertNull(Captcha::make()->uniqueId($uid)->draw());
        $this->assertFalse(Captcha::check($uid,$string));
    }

    public function testSession()
    {
        $string = Captcha::make()->draw();
        $this->assertInternalType('string', $string);
        $this->assertTrue(Captcha::checkSession($string));
        \Spirit\Response\Session::complete();
        $this->assertFalse(Captcha::checkSession($string));
    }

}
