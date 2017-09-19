<?php
return [
    'error/{number?}' => [
        'methods' => ['get','post'],
        'uses' => ['\Spirit\Common\Controllers\ErrorController', 'common']
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
];