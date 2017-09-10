<?php

namespace Spirit\DB\Builder;

class JoinWhere extends Where
{

    public function on($field, $operator = '=', $value = '')
    {
        return $this->where($field, $operator, $value);
    }

    public function orOn($field, $operator = '=', $value = '')
    {
        return $this->orWhere($field, $operator, $value);
    }

    public function rawOn($raw, $fields = [])
    {
        return $this->orWhereRaw($raw, $fields);
    }

    public function orRawOn($raw, $fields = [])
    {
        return $this->whereRaw($raw, $fields);
    }

    public function get()
    {
        $r = implode("\n\t", $this->whereArr);

        return $r;
    }

}