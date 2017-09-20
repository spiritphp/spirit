<?php

namespace Spirit;

use Spirit\Auth\DefaultDriver;
use Spirit\Common\Models\User;
use Spirit\Response\Session\FileHandler;
use Spirit\Structure\Package;
use Spirit\Structure\Plugin;
use Spirit\Structure\Provider;

class Config
{
    public $allconfig = [];
    public $timezone = 'Europe/Moscow';

    /**
     * Экшн который вызывается при ошибках
     *
     * По умолчанию вызывается вот этот
     * @var \Spirit\Common\Controllers\ErrorController
     */
    public $controllerError;

    /**
     * Enable common route from vendor
     * @var bool
     */
    public $enableCommonRoute = true;

    public $defaultDBConnection = DB::DRIVER_POSTGRESQL;

    public $connections = [
        'pgsql' => [
            'driver' => DB::DRIVER_POSTGRESQL,
            'database' => 'DB_NAME',
            'user' => 'DB_USER',
            'password' => 'DB_PASSWORD',
            'host' => 'localhost',
            //'type' => PostgreSQL::TYPE_BOUNCER,
            //'port' => 5432,
            'port' => 6432,
        ],
        'mysql' => [
            'driver' => DB::DRIVER_MYSQL,
            'database' => 'DB_NAME',
            'user' => 'DB_USER',
            'password' => 'DB_PASSWORD',
            'host' => 'localhost',
            'port' => 3306,
        ]

    ];

    public $cache = [
        'default' => 'file',

        'stores' => [
            'file' => [
                'driver' => 'file',
                'path' => 'cache/',
            ],

            'memcached' => [
                'driver' => 'memcached',
                'prefix' => null,
                'servers' => [
                    [
                        'host' => '127.0.0.1',
                        'port' => 11211,
                        'weight' => 100
                    ]
                ]
            ]
        ]
    ];

    public $pause = false;
    public $pauseOpen = null;

    public $appKey = 'd41s9972xw4bln7*(#_!@#6)!@}null!';

    public $error = [

        /**
         * Wrote log
         */
        'log' => true,

        /**
         * Continue write log if file exists
         */
        'continue' => true
    ];

    public $mail = [
        'type' => 'log',
        'log' => [],
        'from' => 'robot@example.com',
        'name' => 'App Name'
    ];

    public $auth = [
        'type' => 'cookie',

        'log' => true,

        'upd_online_per_time' => 180,

        'init' => true,

        'driver' => DefaultDriver::class
    ];

    public $sessionHandlerClass = FileHandler::class;

    public $autoloadMap = [
        'App\\' => 'app\\',
    ];

    public $autoloadFiles = [
        //'__spirit/src/helpers.php'
    ];

    /**
     * @var Plugin[]
     */
    public $plugins = [];

    /**
     * @var Package[]
     */
    public $packages = [];

    /**
     * @var User
     */
    public $userModel = \App\Models\User;

    public function __construct()
    {

    }

    /**
     * @return Config
     */
    public static function current()
    {
        return Engine::cfg();
    }

    public function loadConfig($config, Config $cfg, Constructor $constructor)
    {
        Engine::i()
            ->includeFile($config, [
                'cfg' => $cfg,
                'constructor' => $constructor
            ]);
    }
}

