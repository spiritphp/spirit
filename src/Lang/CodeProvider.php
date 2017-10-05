<?php

namespace Spirit\Lang;

use Spirit\Engine;
use Spirit\Func\Arr;

class CodeProvider
{

    protected $code;
    protected $dir;

    public function __construct($code)
    {
        $this->code = $code;
        $this->dir = Engine::dir()->lang . $code . '/';
    }

    protected function load($file)
    {
        return Engine::i()
            ->includeFile($this->dir . $file . '.php');
    }

    public function get($k, $data = null)
    {
        $arr = explode('.', $k, 2);

        $value = Arr::get($this->load($arr[0]), $arr[1]);

        if (is_null($data)) {
            return $value;
        }

        $prepareData = [];
        foreach($data as $k => $v) {
            if (strpos($k,':') !== 0) {
                $prepareData[':' . $k] = $v;
            } else {
                $prepareData[$k] = $v;
            }
        }

        return strtr($value, $prepareData);
    }
}