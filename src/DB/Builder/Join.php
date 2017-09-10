<?php

namespace Spirit\DB\Builder;

class Join
{

    const TYPE_JOIN = 'JOIN';
    const TYPE_LEFT_JOIN = 'LEFT JOIN';

    protected $joinArr = [

    ];

    protected $bindingParams = [];

    protected function joinInit($type, $table, $self_key, $far_key)
    {
        if (is_callable($self_key)) {
            $callableJoin = new JoinWhere();

            $self_key($callableJoin);

            $j = $callableJoin->get();

            $bindingParams = $callableJoin->getBindParams();
            $this->bindingParams = array_merge($this->bindingParams, $bindingParams);

        } else {
            if (strpos($self_key, '.') === false) {
                $t = $table;
                if (!is_object($table) && preg_match("/\sas\s(.+)$/",$table,$m)) {
                    $t = $m[1];
                }

                $self_key = $t . '.' . $self_key;
            }

            $j = $self_key . ' = ' . $far_key;
        }

        $this->joinArr[] = $type . ' ' . $table . ' ON ' . $j;

        return $this;
    }

    public function join($table, $self_key, $far_key)
    {
        return $this->joinInit(static::TYPE_JOIN, $table, $self_key, $far_key);
    }

    public function leftJoin($table, $self_key, $far_key)
    {
        return $this->joinInit(static::TYPE_LEFT_JOIN, $table, $self_key, $far_key);
    }

    public function get()
    {
        $r = implode("\n\t", $this->joinArr);
        return $r;
    }

    public function getBindParams()
    {
        return $this->bindingParams;
    }
}