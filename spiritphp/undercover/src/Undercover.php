<?php

namespace Spirit\Undercover;

class Undercover {

    protected $config = 'admin';
    protected $defaultConfig = [
        'css' => [
            '--static/services/admin--css',
            'http://fonts.googleapis.com/css?family=Roboto:400,300,700&subset=latin,cyrillic'
        ],
        'js' => [
            '--static/services/admin--js'
        ],
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
        $css = $this->c('css');
        $js = $this->c('js');
        $menu = $this->getMenu();

        $constructor = Constructor::make()
            ->add(function() use ($css) {
                return components\HtmlHead::make()
                    ->title($this->c('title'))
                    ->css($css)
                    ->isMobile()//->favicon('favicon.ico')
                    ->draw();
            })
            ->add(function() {
                return components\Simple::v('{__SPIRIT__}/services/admin/header.php');
            })
            ->add(function() use ($menu) {
                return components\Simple::v('{__SPIRIT__}/services/admin/menu.php', ['menu' => $menu]);
            })
            ->addContent(function($content) {
                return '<div class="content" id="content">' . $content . '</div>';
            })
            ->add(function() use ($js) {
                return components\HtmlEnd::make()
                    ->js($js)
                    ->draw();
            })
            ->addDebug();


        Engine::i()
            ->setConstructor($constructor);
    }

    protected function getMenu()
    {
        $array_menu = $this->c('menu');

        $menu = [];

        foreach($array_menu as $k => $v) {
            if (!U::acl($v['acl']))
                continue;

            $menu[] = [
                'title' => $v['title'],
                'link' => URL::make($v['link'])
            ];

        }

        return $menu;
    }

}