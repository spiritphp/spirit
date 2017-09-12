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

    protected static $menu = [
        [
            'title' => 'Пользователи',
            'link' => '/undercover/users',
            'acl' => 'user'
        ],
    ];

    public static function init()
    {
        $constructor = Constructor::make()
            ->addLayoutContent('undercover::layout')
            ->addDebug();

        Engine::i()
            ->setConstructor($constructor);
    }

    public static function getMenu()
    {
        $menu = [];

        foreach(static::$menu as $k => $v) {
            if (!Auth::user()->acl($v['acl'])) {
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