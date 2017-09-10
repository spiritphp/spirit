<?php

namespace Spirit\Structure\Model;

use Spirit\Func;

trait MutatorTrait
{

    /**
     * Для автопребразований в boolean
     * @var array
     */
    protected $mutatorJson = [];

    /**
     * Для автопребразований в json
     * @var array
     */
    protected $mutatorBoolean = [];

    protected function mutatorGet($field, $value)
    {
        if (in_array($field, $this->mutatorJson)) {
            $v = is_array($value) ? $value : json_decode($value, 1);

            return $v ? $v : [];
        }

        if (in_array($field, $this->mutatorBoolean)) {
            return $value === 't' || $value === true || $value === 1 || $value === '1';
        }

        $mutatorCallbackName = 'get' . Func\Str::toCamelCase($field) . 'Data';
        if (method_exists($this, $mutatorCallbackName)) {
            return $this->$mutatorCallbackName($value);
        }

        return $value;
    }

    protected function mutatorSet($field, $value)
    {
        if (in_array($field, $this->mutatorJson)) {
            return is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
        }

//        if (in_array($field, $this->mutatorBoolean)) {
//            return ($value === 't' || $value === true || $value === 1 || $value === '1');
//        }

        // Mutator Callback
        $mutatorCallbackName = 'set' . Func\Str::toCamelCase($field) . 'Data';
        if (method_exists($this, $mutatorCallbackName)) {
            return $this->$mutatorCallbackName($value);
        }

        return $value;
    }

}