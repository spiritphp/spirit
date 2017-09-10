<?php

namespace Spirit\DB\Builder;

class Having extends Where
{

    public function having($field, $operator = '=', $value = '')
    {
        return $this->where($field, $operator, $value);
    }

    public function orHaving($field, $operator = '=', $value = '')
    {
        return $this->orWhere($field, $operator, $value);
    }

    public function havingRaw($raw, $fields = [])
    {
        return $this->whereRaw($raw, $fields);
    }

}