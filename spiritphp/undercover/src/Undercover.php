<?php

namespace Spirit\Undercover;

use Spirit\Auth;
use Spirit\Constructor;
use Spirit\Engine;
use Spirit\Route;

/**
 * Class Undercover
 * @package Spirit\Undercover
 *
 */
class Undercover
{

    public static function init()
    {
        $constructor = Constructor::make()
            ->addLayoutContent('undercover/layout')
            ->addDebug();

        Engine::i()
            ->setConstructor($constructor);
    }

    public static function getMenu()
    {
        $cfgMenu = Engine::i()->includeFile(Engine::dir()->config_packages . 'undercover.php');

        $menu = [];

        foreach($cfgMenu as $k => $v) {
            if (!Auth::user()->acl($v['role'])) {
                continue;
            }

            if (isset($v['route'])) {
                $v['link'] = Route::makeUrlForAlias($v['route']);
            }

            $menu[] = [
                'title' => $v['title'],
                'link' => $v['link']
            ];

        }

        return $menu;
    }

}