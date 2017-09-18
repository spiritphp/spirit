<?php

namespace Spirit\Route;

use Spirit\Collection;
use Spirit\Structure\Model;
use Spirit\Request;
use Spirit\Structure\Middleware as MiddlewareStructure;

class Routing
{
    use Alias;

    protected $parentPrefixArr = [];
    protected $parentMiddlewareArr = [];
    protected $parentNamespaceArr = [];

    protected $routes = [];


    /**
     * @var Current
     */
    protected $current;

    /**
     * @var MiddlewareProvider
     */
    protected $middleware;

    /**
     * @var callable[]
     */
    protected $binds = [];

    public function __construct($initialRoutes = null)
    {
        if ($initialRoutes) {
            $this->routes = $initialRoutes;
        }

        $this->middleware = new MiddlewareProvider();
    }

    public function post($path, $options)
    {
        $this->match('post', $path, $options);
    }

    public function get($path, $options)
    {
        $this->match('get', $path, $options);
    }

    public function add($path, $options)
    {
        $this->match([
            'post',
            'get'
        ], $path, $options);
    }

    public function match($methods, $path, $options)
    {
        if (!is_array($methods)) {
            $methods = [$methods];
        }

        $id = null;
        $uses = null;
        $current_middleware = $this->parentMiddlewareArr;

        if (is_array($options) && isset($options['uses'])) {
            if (isset($options['as'])) {
                $id = $options['as'];
            }

            if (isset($options['middleware'])) {
                $middleware = $options['middleware'];
                if (is_string($middleware)) {
                    $middleware = [$middleware];
                }

                $current_middleware += $middleware;
            }

            $uses = $options['uses'];
        } else {
            $uses = $options;
        }

        if (count($this->parentPrefixArr)) {
            $path = implode('/', $this->parentPrefixArr) . ($path !== '/' && $path !== '' ? '/' . $path : '');
        }

        if (count($this->parentNamespaceArr)) {
            $namespace = implode('\\', $this->parentNamespaceArr) . '\\';

            if (is_array($uses)) {
                $uses[0] = $namespace . $uses[0];
            } else if (is_string($uses)) {
                $uses = $namespace . $uses;
            }
        }

        $this->routes[$path] = [
            //'path' => $path,
            'alias' => $id,
            'methods' => $methods,
            'uses' => $uses,
            'middleware' => $current_middleware,
        ];

        if ($id) {
            $this->alias[$id] = $path;
        }
    }

    public function group($options, $callback)
    {
        $groupMiddleware = null;
        $groupNamespace = null;

        if (is_string($options)) {
            $prefix = $options;
        } else {
            $prefix = isset($options['prefix']) ? $options['prefix'] : '';
            $groupMiddleware = isset($options['middleware']) ? $options['middleware'] : null;
            $groupNamespace = isset($options['namespace']) ? $options['namespace'] : null;

            if (is_string($groupMiddleware)) {
                $groupMiddleware = [$groupMiddleware];
            }
        }

        $oldMiddleware = $this->parentMiddlewareArr;

        if ($groupMiddleware) {
            $this->parentMiddlewareArr = array_merge($groupMiddleware, $this->parentMiddlewareArr);
        }

        if ($groupNamespace) {
            $this->parentNamespaceArr[] = $groupNamespace;
        }

        if ($prefix) {
            $this->parentPrefixArr[] = $prefix;
        }

        $callback();

        if ($prefix) {
            array_pop($this->parentPrefixArr);
        }

        if ($groupMiddleware) {
            $this->parentMiddlewareArr = $oldMiddleware;
        }

        if ($groupNamespace) {
            array_pop($this->parentNamespaceArr);
        }
    }

    public function getRoutes()
    {
        $routes = $this->routes;

        foreach($routes as $path => &$route) {
            if (is_object($route['uses']) && ($route['uses'] instanceof \Closure)) {
                $route['uses'] = 'function(){}';
            }
        }
        unset($route);

        return Collection::make($routes);
    }



    /**
     * @param bool $path
     * @return Current|null
     */
    public function parse($path = false)
    {
        if (!$path) {
            $path = '/';
        }

        $route = null;
        if (isset($this->routes[$path])) {
            $cfg = $this->routes[$path];
            $cfg['path'] = $path;
            $route = $this->matchRoute($path, $cfg);
        } elseif (!$route) {
            foreach($this->routes as $routeId => $routeConfig) {
                $routeConfig['path'] = $routeId;
                if ($route = $this->matchRoute($path, $routeConfig)) {
                    break;
                }
            }
        }

        return $this->current = $route;
    }

    /**
     * @param string|array $key
     * @param callable|MiddlewareStructure $middlewareClassName
     */
    public function addMiddleware($key, $middlewareClassName = null)
    {
        $this->middleware->add($key, $middlewareClassName);
    }

    public function bind($key, callable $callback)
    {
        $this->binds[$key] = $callback;
    }

    /**
     * @param $key
     * @param Model|mixed $model
     */
    public function bindModel($key, $model)
    {
        $this->binds[$key] = function ($v) use ($model) {
            return $model::find($v);
        };
    }

    /**
     * @return Current
     */
    public function current()
    {
        return $this->current;
    }

    protected function checkMiddleware($routeConfig)
    {
        if (!isset($routeConfig['middleware']))
            return true;

        if (!$routeConfig['middleware'] || count($routeConfig['middleware']) == 0) {
            return true;
        }

        return $this->middleware->check($routeConfig['middleware']);
    }

    /**
     * @param $path
     * @param $routeConfig
     * @return null|Current
     * @throws \Exception
     */
    protected function matchRoute($path, $routeConfig)
    {
        $routePath = $routeConfig['path'];

        $routeMatch = preg_replace("/\{[^\{\}]+\}/", '(.+?)', $routePath);

        if (!preg_match("/^" . str_replace('/', '\/', $routeMatch) . "$/", $path, $path_m)) {

            if (!strpos($routePath, '?}') !== false) {
                return null;
            }

            $routeMatch = preg_replace("/\/\(\.\+\?\)$/", '', $routeMatch);

            if (!preg_match("/^" . str_replace('/', '\/', $routeMatch) . "$/", $path)) {
                return null;
            }

            $path_vars = [null];
        } else {
            unset($path_m[0]);
            $path_vars = array_values($path_m);
        }

        $method = Request::getMethod();
        $methodExist = false;
        foreach($routeConfig['methods'] as $allowMethod) {
            if (strtoupper($allowMethod) === $method) {
                $methodExist = true;
                break;
            }
        }

        if (!$methodExist) {
            throw new \Exception('Method is not found');
        }

        if ($this->checkMiddleware($routeConfig) !== true) {
            return null;
        }

        preg_match_all("/\{([^\{\}]+)\}/ius", $routePath, $m);
        $route_vars = $m[1];

        $error = false;
        $vars = [];
        $binds = [];
        foreach($route_vars as $i => $r_var) {

            $r_var_arr = explode(':', $r_var, 2);

            $field = $r_var_arr[0];
            $field_for_key = str_replace('?', '', $field);
            $regexp = isset($r_var_arr[1]) ? $r_var_arr[1] : null;

            if (array_key_exists($field_for_key, $this->binds)) {
                $path_vars[$i] = $this->binds[$field_for_key]($path_vars[$i]);
                $binds[] = $field_for_key;
            }

            if (strpos($field, '?') === false && !isset($path_vars[$i])) {
                $error = 1;
                break;
            }

            if (strpos($field, '?') !== false && !isset($path_vars[$i])) {
                $vars[$field_for_key] = null;
                continue;
            }

            if ((is_string($path_vars[$i]) || is_numeric($path_vars[$i])) && $regexp && !preg_match("/^" . $regexp . "$/", $path_vars[$i])) {
                $error = 2;
                break;
            }

            $vars[$field_for_key] = $path_vars[$i];
        }

        if ($error) {
            return null;
        }

        $uses = $routeConfig['uses'];

        if (is_string($uses)) {
            $uses = explode('@', $uses);
        }

        return Current::make([
            'call' => $uses,
            'vars' => $vars,
            'path' => $routePath,
            'config' => [
                'alias' => isset($routeConfig['alias']) ? $routeConfig['alias'] : null,
                'binds' => $binds,
                'path' => $routeConfig['path'],
                'middleware' => isset($routeConfig['middleware']) ? $routeConfig['middleware'] : null
            ]
        ]);
    }
}