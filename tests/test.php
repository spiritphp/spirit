<?php
require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../src/Engine.php';

\Spirit\Engine::run(__DIR__ . '/../', function ($cfg, $constructor) {
    /**
     * \Spirit\Config $cfg
     */
    $cfg->defaultDBConnection = env('DATABASE_DRIVER');
    $cfg->connections['pgsql']['database'] = env('PGSQL_DATABASE');
    $cfg->connections['pgsql']['user'] = env('PGSQL_USER');
    $cfg->connections['pgsql']['password'] = env('PGSQL_PASSWORD');

    $cfg->connections['mysql']['database'] = env('MYSQL_DATABASE');
    $cfg->connections['mysql']['user'] = env('MYSQL_USER');
    $cfg->connections['mysql']['password'] = env('MYSQL_PASSWORD');
});