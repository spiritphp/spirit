<?php
return [
    'error/{number?}' => [
        'methods' => ['get','post'],
        'uses' => ['\Spirit\Common\Controllers\ErrorController', 'common']
    ],
    'debug/phpinfo' => [
        'methods' => ['get'],
        'uses' => ['\Spirit\Common\Controllers\DebugController', 'phpinfo'],
        'middleware' => ['role:debug']
    ],
    'debug/checkmail' => [
        'methods' => ['get', 'post'],
        'uses' => ['\Spirit\Common\Controllers\DebugController', 'checkmail'],
        'middleware' => ['role:debug']
    ],
    'captcha/{unique_id}' => [
        'methods' => ['post', 'get'],
        'uses' => ['AuthController', 'captcha'],
    ],
    '--static/{path}' => [
        'methods' => ['get'],
        'uses' => ['\Spirit\Common\Controllers\AssetsController', 'read'],
    ],
    'login' => [
        'methods' => ['post', 'get'],
        'uses' => ['AuthController', 'login'],
        'middleware' => ['guest']
    ],
    'logout' => [
        'methods' => ['post', 'get'],
        'uses' => ['AuthController', 'logout'],
        'middleware' => ['auth']
    ],
    'registration' => [
        'methods' => ['post', 'get'],
        'uses' => ['AuthController', 'registration'],
        'middleware' => ['guest']
    ],
    'recovery/{hash?}' => [
        'methods' => ['post', 'get'],
        'uses' => ['AuthController', 'recovery'],
        'middleware' => ['guest']
    ],
    'auth/{type?}' => [
        'methods' => ['post', 'get'],
        'uses' => ['AuthController', 'auth'],
        'middleware' => ['guest']
    ],
    'activation/{code}' => [
        'methods' => ['post', 'get'],
        'uses' => ['AuthController', 'activation'],
        'middleware' => ['guest']
    ],

    // ADMIN
    'undercover' => [
        'methods' => ['post', 'get'],
        'uses' => ['\Spirit\Common\Controllers\AdminController', 'startAdmin'],
        'middleware' => ['role:panel']
    ],

    // USER
    'undercover/users' => [
        'methods' => ['post', 'get'],
        'uses' => ['\Spirit\Common\Controllers\UserController', 'usersAdmin'],
        'middleware' => ['role:users']
    ],
    'undercover/user/{user:\d+}' => [
        'methods' => ['post', 'get'],
        'uses' => ['\Spirit\Common\Controllers\UserController', 'userAdmin'],
        'middleware' => ['role:users']
    ],
    'undercover/user/{id:\d+}/data' => [
        'methods' => ['post', 'get'],
        'uses' => ['\Spirit\Common\Controllers\UserController', 'userDataAdmin'],
        'middleware' => ['role:users']
    ],
    'undercover/user/{id:\d+}/role' => [
        'methods' => ['post', 'get'],
        'uses' => ['\Spirit\Common\Controllers\UserController', 'userRoleAdmin'],
        'middleware' => ['role:user_acl']
    ],
    'undercover/user/{id:\d+}/info' => [
        'methods' => ['post', 'get'],
        'uses' => ['\Spirit\Common\Controllers\UserController', 'userInfoAdmin'],
        'middleware' => ['role:users']
    ],
    'undercover/user/{id:\d+}/apps' => [
        'methods' => ['post', 'get'],
        'uses' => ['\Spirit\Common\Controllers\UserController', 'userAppsAdmin'],
        'middleware' => ['role:users']
    ],

    // LOGS
    'undercover/logs' => [
        'methods' => ['get'],
        'uses' => ['\Spirit\Common\Controllers\DebugController', 'logsAdmin'],
        'middleware' => ['role:debug']
    ],

    // CLEAR
    'undercover/clean/{type?}' => [
        'methods' => ['get', 'post'],
        'uses' => ['\Spirit\Common\Controllers\CleanController', 'cleanAdmin'],
        'middleware' => ['role:clean']
    ],
];