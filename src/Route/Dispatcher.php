<?php
namespace Spirit\Route;


use Spirit\Request;
use Spirit\Structure\Model;
use Spirit\Response;

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

        foreach($parameters as $parameter) {
            if($ref_class = $parameter->getClass()) {
                if ($ref_class->name === Request::class) {
                    $prepareVars[] = Request::getInstance();
                } else if ($ref_class->isSubclassOf(new \ReflectionClass(Model::class))) {
                    if (count($vars) === 0) {
                        break;
                    }

                    $method = $ref_class->getMethod('find');
                    $newValue = $method->invoke(null, array_shift($vars));

                    if (is_null($newValue)) {
                        return null;
                    }

                    $prepareVars[] = $newValue;
                }

            } else {
                if (count($vars) === 0) {
                    break;
                }

                $prepareVars[] = array_shift($vars);
            }
        }

        return $prepareVars;
    }

    public  function logs()
    {
        return $this->logs;
    }

}