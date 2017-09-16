<?php

Route::group([
    'middleware' => \Spirit\Undercover\UndercoverMiddleware::class,
    'prefix' => 'undercover',
],function(){

    Route::add('',['UndercoverController', 'index']);

});