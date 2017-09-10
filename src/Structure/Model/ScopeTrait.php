<?php

namespace Spirit\Structure\Model;

use Spirit\Func;

trait ScopeTrait
{

    protected $scopes = [

    ];

    protected $withoutScopes = [

    ];

    /**
     * @param $method
     * @param $args
     * @return $this|null
     */
    protected function scopeCall($method, $args)
    {
        $scopeMethod = 'scope' . Func\Str::toCamelCase($method);
        if (method_exists($this, $scopeMethod)) {
            $this->{$scopeMethod}(...$args);
            return $this;
        }

        return null;
    }

    protected function scopeAdd()
    {
        if (count($this->scopes) == 0) return;

        foreach ($this->scopes as $scope) {
            if (in_array($scope, $this->withoutScopes)) continue;

            $scopeMethod = 'scope' . Func\Str::toCamelCase($scope);
            $this->$scopeMethod();
        }
    }
}