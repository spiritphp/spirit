<?php

namespace Spirit\Error;

use Spirit\Config\Cfg;
use Spirit\DB;
use Spirit\Func\Str;

abstract class LogAbstract
{

    use Cfg;

    public static function make(Info $info)
    {
        return new static($info);
    }

    /**
     * @var Info
     */
    protected $info;
    protected $queries;

    public function __construct(Info $info)
    {
        $this->info = $info;
    }

    protected function tableInfo()
    {
        $data = $this->info->except(['trace','traceFull']);

        $newData = [];
        foreach($data as $k => $v) {
            if (!$v) {
                continue;
            }

            $newData[] =  '' . strtoupper($k) . ": " . Str::toString($v) . "";
        }

        return implode("\n", $newData);
    }

    protected function getQueries()
    {
        if ($this->queries) return $this->queries;

        $queries = [];
        foreach(DB::getAllQueries() as $q) {
            $queries[] = [
                'query' => trim(preg_replace("/\s+/ius", ' ', $q['query'])),
                'map' => $q['map'],
                'time' => round($q['time'], 6)
            ];
        }

        return $this->queries = $queries;
    }

    protected function tableQuery()
    {
        $queries = $this->getQueries();

        if (count($queries) < 1) return '';

        $newData = [];
        foreach($queries as $k => $q) {
            $query = implode("\n", str_split($q['query'], 120));
            $newData[] =  '#' . ($k + 1) . ' ' . $q['map'] . ' - ' . $q['time'] . "\n" .
                $query
            ;
        }

        return "\n-- QUERIES --\n" . implode("\n", $newData);
    }

    protected function tableTrace($data)
    {
        $new_data = array_map(function($key, $value) {
            return '#' . $key . ' ' . $value;// . "\n" . str_repeat('-', 50);
        }, array_keys($data), $data);

        return "\n-- TRACE --\n" . implode("\n", $new_data);
    }
}