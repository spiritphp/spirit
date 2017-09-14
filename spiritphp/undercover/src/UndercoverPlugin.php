<?php

namespace Spirit\Undercover;

use Spirit\Route;
use Spirit\Structure\Plugin;

class UndercoverPlugin extends Plugin {

    protected function boot()
    {
        Route::add('undercover',[
            'uses' => ['Admin\UndercoverController', 'index'],
            'middleware' => ['role:panel'],
            'as' => 'admin'
        ]);
    }
}