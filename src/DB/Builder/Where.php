<?php

namespace Spirit\DB\Builder;

use Spirit\DB;

/**
 * Class Where
 * @package Spirit\DB\Builder
 */
class Where
{

    const TYPE = 'type';
    const TYPE_AND = 'AND';
    const TYPE_OR = 'OR';

    const IS_RAW = 'RAW';
    const IS_NULL = 'IS_NULL';
    const IS_NOT_NULL = 'IS_NOT_NULL';

    protected static $operators = [
        '=',
        '>=', '>',
        '<=', '<',
        '<>',
        'LIKE'
    ];

    protected $whereArr = [];
    protected $bindingParams = [];

    protected function add($type, $field, $operator, $value = false)
    {
        if (is_array($field)) {

            if (isset($field[0])) {
                foreach ($field as $v) {
                    $this->add($type, $v, $operator, $value);
                }
            } else {
                foreach ($field as $k => $v) {
                    $this->add($type, $k, $operator, $v);
                }
            }

            return $this;
        }

        if (!is_string($field) && is_callable($field)) {
            $callableWhere = new Where();
            $field($callableWhere);

            $w = '(' . $callableWhere->get() . ')';

            $bindingParams = $callableWhere->getBindParams();
            $this->bindingParams = array_merge($this->bindingParams, $bindingParams);

        } elseif ($operator === static::IS_RAW) {
            $w = $field;

        } elseif ($operator === static::IS_NULL) {
            $w = $field . ' IS NULL';

        } elseif ($operator === static::IS_NOT_NULL) {
            $w = $field . ' IS NOT NULL';

        } else {
            if (!in_array($operator, static::$operators, true)) {
                $value = $operator;
                $operator = '=';
            }

            if (is_object($value) && $value instanceof db\Raw) {
                $w = $field . ' ' . $operator . ' ' . (string)$value;
            } else {
                $w = $field . ' ' . $operator . ' ?';
                $this->bindingParams[] = $value;
            }
        }


        $this->whereArr[] = (count($this->whereArr) > 0 ? $type . ' ' : '') . $w;

        return $this;
    }

    public function where($field, $operator = '=', $value = '')
    {
        return $this->add(static::TYPE_AND, $field, $operator, $value);
    }

    public function orWhere($field, $operator = '=', $value = '')
    {
        return $this->add(static::TYPE_OR, $field, $operator, $value);
    }

    public function whereRaw($raw, $fields = [], $type = self::TYPE_AND)
    {
        if (count($fields)) {
            $this->bindingParams = array_merge($this->bindingParams, $fields);
        }

        return $this->add($type, $raw, static::IS_RAW);
    }

    public function orWhereRaw($raw, $fields = [])
    {
        return $this->whereRaw($raw, $fields, static::TYPE_OR);
    }

    public function whereNull($field)
    {
        return $this->add(static::TYPE_AND, $field, static::IS_NULL);
    }

    public function orWhereNull($field)
    {
        return $this->add(static::TYPE_OR, $field, static::IS_NULL);
    }

    public function whereNotNull($field)
    {
        return $this->add(static::TYPE_AND, $field, static::IS_NOT_NULL);
    }

    public function orWhereNotNull($field)
    {
        return $this->add(static::TYPE_OR, $field, static::IS_NOT_NULL);
    }

    protected function addWhereIn($type, $field, $values = [], $not = false)
    {
        if (is_string($values)) {
            $w = preg_replace("/(\s{2,}|\n)/ius", ' ', $values);

        } else {
            $prepareValues = [];
            foreach ($values as $value) {
                if (is_object($value) && $value instanceof db\Raw) {
                    $prepareValues[] = (string)$value;
                } else {
                    $prepareValues[] = '?';
                    $this->bindingParams[] = $value;
                }
            }
            $w = implode(',', $prepareValues);

        }

        $wRaw = $field . ($not ? ' NOT' : '') . ' IN (' . $w . ')';

        return $this->whereRaw($wRaw, [], $type);
    }

    public function whereIn($field, $values = [])
    {
        return $this->addWhereIn(static::TYPE_AND, $field, $values);
    }

    public function orWhereIn($field, $values = [])
    {
        return $this->addWhereIn(static::TYPE_OR, $field, $values);
    }

    public function whereNotIn($field, $values = [])
    {
        return $this->addWhereIn(static::TYPE_AND, $field, $values, true);
    }

    public function orWhereNotIn($field, $values = [])
    {
        return $this->addWhereIn(static::TYPE_OR, $field, $values, true);
    }

    public function get()
    {
        $r = implode("\n\t", $this->whereArr);

        return $r;
    }

    public function getBindParams()
    {
        return $this->bindingParams;
    }
}