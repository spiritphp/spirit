<?php

namespace Spirit\Constructor\Components;

use Spirit\Engine;
use Spirit\DB;
use Spirit\FileSystem;
use Spirit\Request;
use Spirit\Route;
use Spirit\Structure\Component;
use Spirit\Func;

use \Spirit\Cache;

class Debug extends Component
{

    public static function v($view = null, $data = [])
    {
        if (is_null($view)) {
            $view = '{__SPIRIT__}/components/debug.php';
        }

        if (count(func\Trace::getTraceAfter()) > 0) {
            $data['after_trace'] = func\Trace::getTraceAfter();
        }

        $data['route'] = Request::fullPath();

        $data['get'] = $_GET;
        $data['post'] = $_POST;
        $data['session'] = $_SESSION;
        $data['cookie'] = $_COOKIE;
        $data['files'] = $_FILES;

        $data['server'] = $_SERVER;

        $data['all_classes'] = Engine::getAutoloadFiles();

        $_query = array();

        foreach (DB::getAllQueries() as $value) {
            $_query[] = array(
                'query' => preg_replace("/\t/", " ", $value['query']),
                'time' => $value['time'],
                'memory' => $value['memory2'] . ' (' . $value['memory1'] . ')',
                'map' => isset($value['map']) ? $value['map'] : '',
            );
        }

        $data['query'] = $_query;


        // Cache
        if (count(Cache::store('file')->getStatGet()) || count(Cache::store('file')->getStatPut())) {
            $data['file_cache'] = [
                'use' => [],
                'new' => [],
            ];

            $__cache = [];
            $__cache['use'] = [];
            $__cache['new'] = [];

            foreach (Cache::store('file')->getStatGet() as $value) {
                $__cache['use'][] = $value;

            }

            foreach (Cache::store('file')->getStatPut() as $value) {
                $__cache['new'][] = $value;
            }

            $data['file_cache'] = $__cache;
        }

        // Memcache
        if (count(Cache::store('memcached')->getStatGet()) || count(Cache::store('memcached')->getStatPut())) {
            $data['memory_cache'] = [
                'use' => [],
                'new' => [],
            ];

            $__cache = [];
            $__cache['use'] = [];
            $__cache['new'] = [];

            foreach (Cache::store('memcached')->getStatGet()as $value) {
                $__cache['use'][] = $value;
            }

            foreach (Cache::store('memcached')->getStatPut() as $value) {
                $__cache['new'][] = $value;
            }

            $data['memory_cache'] = $__cache;
        }

        // Config
        $data['load_cfg'] = Engine::cfg()->allconfig;

        // Controller
        $data['controller'] = Engine::getControllerLog();
        $data['route'] = Route::current();
        $data['styles'] = FileSystem::get(Engine::dir()->spirit_public . 'css/components/debug.css');
        $data['scripts'] = FileSystem::get(Engine::dir()->spirit_public . 'js/components/debug.js');

        return static::getInstance()->view($view, $data);
    }

}