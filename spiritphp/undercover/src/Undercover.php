<?php

namespace Spirit\Undercover;

use Spirit\Auth;
use Spirit\Constructor;
use Spirit\Engine;
use Spirit\Request\URL;
use Spirit\Structure\Service;

class Undercover extends Service {

    protected $config = 'admin';
    protected $defaultConfig = [
        'title' => 'Панель управления',
        'menu' => [
            [
                'title' => 'Пользователи',
                'link' => 'undercover/users',
                'acl' => 'user'
            ],
            [
                'title' => 'Логи',
                'link' => 'undercover/logs',
                'acl' => 'debug'
            ],
            [
                'title' => 'Очиститель',
                'link' => 'undercover/clean',
                'acl' => 'clean'
            ]
        ]
    ];

    public static function init()
    {
        static::getInstance()
            ->setAdminConstructor();
    }

    protected function setAdminConstructor()
    {
        $menu = $this->getMenu();

        $constructor = Constructor::make()
            ->addLayoutContent('undercover::layout')
            ->addDebug();


        Engine::i()
            ->setConstructor($constructor);
    }

    protected function getMenu()
    {
        $array_menu = $this->c('menu');

        $menu = [];

        foreach($array_menu as $k => $v) {
            if (!Auth::user()->acl($v['acl'])) {
                continue;
            }

            $menu[] = [
                'title' => $v['title'],
                'link' => URL::make($v['link'])
            ];

        }

        return $menu;
    }

}