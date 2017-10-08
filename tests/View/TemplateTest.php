<?php

namespace Tests\View;

use PHPUnit\Framework\TestCase;

/**
 * @covers DB
 */
class TemplateTest extends TestCase
{

    public function testDefault()
    {
        $render = \Spirit\View::make(__DIR__ . '/resources/default/view.php',['value' => 'A'])->render();

        $render = preg_replace("/\s/","",$render);

        $this->assertEquals('<div>A</div>', $render);
    }

    public function testAppend()
    {
        $render = \Spirit\View::make(__DIR__ . '/resources/append/view.php',['value1' => 'A','value2' => 'B'])->render();

        $render = preg_replace("/\s/","",$render);

        $this->assertEquals('<div>AB</div>', $render);
    }

    public function testPrepend()
    {
        $render = \Spirit\View::make(__DIR__ . '/resources/prepend/view.php',['value1' => 'A','value2' => 'B'])->render();

        $render = preg_replace("/\s/","",$render);

        $this->assertEquals('<div>BA</div>', $render);
    }

    public function testView()
    {
        $render = \Spirit\View::make(__DIR__ . '/resources/view/layout.php',['value1' => 'A','value2' => 'B'])
            ->render();

        $render = preg_replace("/\s/","",$render);

        $this->assertEquals('<div>A<b>B</b></div>', $render);
    }

    public function testCombine()
    {
        $render = \Spirit\View::make(__DIR__ . '/resources/combine/view.php',['value1' => 'A','value2' => 'B'])
            ->render();

        $render = preg_replace("/\s/","",$render);

        $this->assertEquals('<div>A</div><div>B</div>', $render);
    }

    public function testDoubleExt()
    {
        $render = \Spirit\View::make(__DIR__ . '/resources/double_ext/view.php',['value1' => 'A','value2' => 'B'])
            ->render();

        $render = preg_replace("/\s/","",$render);

        $this->assertEquals('<div>A<div>B</div></div>', $render);
    }

}
