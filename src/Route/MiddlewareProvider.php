<?php

namespace Spirit\Route;

use Spirit\Structure\Middleware as MiddlewareStructure;
use Spirit\Route\Middleware;

class MiddlewareProvider
{

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
     * @param callable|MiddlewareStructure|array $middlewareClassName
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

            $vars = null;
            if (is_string($middleware) && strpos($middleware, ':') !== false) {
                $vars = explode(':', $middleware, 2);
                $middleware = $vars[0];
                $vars = $vars[1];
            }

            /**
             * @var MiddlewareStructure|callable $middlewareCallback
             */
            if (is_string($middleware) && isset($this->middleware[$middleware])) {
                $middlewareCallback = $this->middleware[$middleware];
            } else {
                $middlewareCallback = $middleware;
            }

            if (is_array($middlewareCallback)) {
                $result = $this->check($middlewareCallback);
            } else if (is_callable($middlewareCallback)) {
                $result = $middlewareCallback($vars);
            } else {
                $result = $middlewareCallback::getInstance()->handle($vars);
            }

            if ($result !== true) {
                $result = false;
                break;
            }
        }

        return $result;
    }

}