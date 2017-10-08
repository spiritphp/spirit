<?php

namespace Tests\Constructor;

use PHPUnit\Framework\TestCase;
use Spirit\Constructor;

function spaceFree($string)
{
    return trim(preg_replace("/\s/", '', $string));
}

final class ConstructorTest extends TestCase
{
    public function testAdd()
    {
        $c = Constructor::make()
            ->add('<div>asd</div>')
            ->add('<div>qwe</div>');

        $this->assertEquals("<div>asd</div><div>qwe</div>", spaceFree($c->compile()));

        $c = Constructor::make()
            ->add('asd', '<div>asd</div>')
            ->add('qwe', '<div>qwe</div>');

        $this->assertEquals("<div>asd</div><div>qwe</div>", spaceFree($c->compile()));
    }

    public function testAddCallback()
    {
        $c = Constructor::make()
            ->add(function () {
                return '<div>asd</div>';
            });

        $this->assertEquals("<div>asd</div>", spaceFree($c->compile()));
    }

    public function testIsJson()
    {
        $c = Constructor::make()
            ->add('name', 'Marat')
            ->add('surname', 'Nuriev')
            ->isJSON();

        $this->assertEquals('{"name":"Marat","surname":"Nuriev"}', $c->compile());
    }

    public function testContent()
    {
        $c = Constructor::make()
            ->addContent();

        $this->assertEquals('A', $c->setContent('A')->compile());

        $c = Constructor::make()
            ->addContent(function($response) {
                return "<div>{$response}</div>";
            });

        $this->assertEquals('<div>A</div>', $c->setContent('A')->compile());
    }

    public function testContentLayout()
    {
        $c = Constructor::make()
            ->addLayoutContent(__DIR__ . '/resources/layout');

        $this->assertEquals('<div>A</div><div></div>', spaceFree($c->setContent('A')->compile()));

        $c = Constructor::make()
            ->addLayoutContent(__DIR__ . '/resources/layout','other_name');

        $this->assertEquals('<div></div><div>A</div>', spaceFree($c->setContent('A')->compile()));
    }
}
