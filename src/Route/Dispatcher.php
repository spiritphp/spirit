<?php
namespace Spirit\Route;

use Spirit\Error;
use Spirit\Structure\Model;
use Spirit\Response;
use Spirit\Structure\Arrayable;
use Spirit\Structure\Jsonable;
use Spirit\View;

class Dispatcher {

    public static function make(Current $route)
    {
        return new static($route);
    }

    /**
     * @var Current
     */
    protected $route;
    protected $logs = [];

    public function __construct(Current $route)
    {
        $this->route = $route;
    }

    /**
     * @return Response|Response\Redirect
     */
    public function response()
    {
        $response = null;
        $vars = $this->route->vars;

        $logs = [];

        if (!$this->route->callable) {
            $className = $this->route->className;
            $methodName = $this->route->methodName;

            $class = new $className();
            $logs['class'] = $className;
            $logs['method'] = $methodName;

            $vars = $this->prepareVars($vars, new \ReflectionMethod($class, $methodName));

            if (!is_null($vars)) {
                $response = $class->{$methodName}(...$vars);
            }

        } else {
            $logs['callback'] = true;

            $vars = $this->prepareVars($vars, new \ReflectionFunction($this->route->call));

            if (!is_null($vars)) {
                $callback = $this->route->call;
                $response = $callback(...$vars);
            }
        }

        $logs['vars'] = $vars;

        $this->logs = $logs;

        if (!is_null($response)) {

            if (!($response instanceof Response\Redirect) && !($response instanceof Response)) {
                $response = Response::make($response);
            }

        }

        return $response;
    }

    /**
     * @param array $vars
     * @param \ReflectionMethod|\ReflectionFunction|\ReflectionFunctionAbstract $reflection
     * @return array
     */
    protected function prepareVars($vars, $reflection)
    {
        $prepareVars = [];
        $parameters = $reflection->getParameters();
        foreach (array_values($vars) as $k => $v) {
            if (isset($parameters[$k])) {
                if($ref_class = $parameters[$k]->getClass()) {
                    $v = $this->prepareVarClass($ref_class, $v);

                    if (is_null($v)) {
                        return null;
                    }
                }
            }

            $prepareVars[] = $v;
        }

        return $prepareVars;
    }

    protected function prepareVarClass(\ReflectionClass $class, $v)
    {
        $cl = new \ReflectionClass(Model::class);

        if ($class->isSubclassOf($cl)) {
            $method = $class->getMethod('find');
            $v = $method->invoke(null, $v);
        }

        return $v;
    }

    public  function logs()
    {
        return $this->logs;
    }

}