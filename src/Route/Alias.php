<?php

namespace Spirit\Route;

use Spirit\Engine;

/**
 * Trait Alias
 * @package Spirit\Route
 * @mixin Routing
 */
trait Alias
{

    protected $alias = [];

    /**
     * @param $id
     * @param array $vars
     * @param bool $withHost
     * @return string
     * @throws \Exception
     */
    public function makeUrlForAlias($id, $vars = [], $withHost = false)
    {
        if (!isset($this->alias[$id])) {
            throw new \Exception('Not found route for alias ' . $id);
        }

        if (!is_array($vars)) {
            $vars = [$vars];
        }

        $path = $this->alias[$id];

        preg_match_all("/\{([^\{\}]+)\}/ius", $path, $m);
        $route_vars = $m[1];

        $replaceArr = [];
        foreach($route_vars as $i => $r_var) {

            $r_var_arr = explode(':', $r_var, 2);
            $field = $r_var_arr[0];
            $field_for_key = str_replace('?', '', $field);

            $value = null;

            if (count($vars) > 0) {
                if (isset($vars[$field_for_key])) {
                    $value = $vars[$field_for_key];
                    unset($vars[$field_for_key]);
                } else {
                    $value = array_shift($vars);
                }
            }

            if (is_null($value) && strpos($field, '?') === false) {
                throw new \Exception('Required params «' . $field . '» for alias ' . $id);
            }

            $replace_key = '{' . $r_var . '}';

            $replaceArr[$replace_key] = $value;
        }

        $url = strtr($path, $replaceArr) . (count($vars) ? '?' . http_build_query($vars) : '');

        return ($withHost ? Engine::i()->url : '/') . ltrim(rtrim($url, '/'), '/');
    }
}