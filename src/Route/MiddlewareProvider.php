<?php

namespace Spirit\Route;

use Spirit\Structure\Middleware as MiddlewareStructure;
use Spirit\Route\Middleware;

class MiddlewareProvider {

    /**
     * @var MiddlewareStructure[]|callable[]
     */
    protected $middleware = [
        'auth' => Middleware\Auth::class,
        'guest' => Middleware\Guest::class,
        'role' => Middleware\Role::class,
        'throttle' => Middleware\Throttle::class,
        'token' => Middleware\Token::class,
    ];

    public function __construct()
    {
    }

    /**
     * @param $key
     * @param callable|MiddlewareStructure $middlewareClassName
     */
    public function add($key, $middlewareClassName)
    {
        $this->middleware[$key] = $middlewareClassName;
    }

    /**
     * @param array|string $middlewareList
     * @return bool|false|true
     */
    public function check($middlewareList)
    {
        if (!is_array($middlewareList)) {
            $middlewareList = [$middlewareList];
        }

        $result = true;
        foreach($middlewareList as $middleware) {
            $vars = [];
            if (strpos($middleware, ':') !== false) {
                $vars = explode(':', $middleware);
                $middleware = $vars[0];
                unset($vars[0]);
            }

            /**
             * @var MiddlewareStructure|callable $middlewareCallback
             */
            $middlewareCallback = $this->middleware[$middleware];

            if (is_callable($middlewareCallback)) {
                $result = $middlewareCallback(...$vars);
            } else {
                $result = $middlewareCallback::getInstance()->handle(...$vars);
            }

            if ($result !== true) {
                $result = false;
                break;
            }
        }

        return $result;
    }
}