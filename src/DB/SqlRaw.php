<?php

namespace Spirit\DB;

class SqlRaw {

    public static function make($sql, $bindParams = [])
    {
        return new static($sql, $bindParams);
    }

    protected $sql;
    protected $bindParams;

    /**
     * @var string
     */
    protected $str;

    public function __construct($sql, $bindParams)
    {
        $this->sql = $sql;
        $this->bindParams = $bindParams;
    }

    /**
     * @return string
     */
    public function toString()
    {
        if ($this->str) return $this->str;

        $vars = $this->bindParams;
        reset($vars);
        $this->str = preg_replace_callback("/(\s|,|\()(\?|\:[a-z\_\-0-9]+)/i",function($m) use (&$vars) {
            //dd($m);
            $key = $m[2];
            if (strpos($key,':') === 0) {

                if (isset($vars[$key])) {
                    $v = $vars[$key];
                    unset($vars[$key]);
                } else {
                    $v = $vars[substr($key,1)];
                    unset($vars[substr($key,1)]);
                }

                reset($vars);
                return $m[1] . $v;
            } else {
                $v = null;
                foreach ($vars as $k => $v) {
                    unset($vars[$k]);
                    break;
                }
                reset($vars);

                return $m[1] . $v;
            }

        }, $this->sql);

        return $this->str;
    }

    public function __toString()
    {
        return $this->toString();
    }
}