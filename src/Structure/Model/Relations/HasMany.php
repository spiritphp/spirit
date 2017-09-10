<?php

namespace Spirit\Structure\Model\Relations;

use Spirit\DB;
use Spirit\Structure\Model;

class HasMany extends Relation
{
    static $isType = self::IS_ARRAY;

    protected $localKey;
    protected $foreignKey;

    public function setLocalKey($v)
    {
        $this->localKey = $v;
        return $this;
    }

    public function setForeignKey($v)
    {
        $this->foreignKey = $v;
        return $this;
    }

    /**
     * @param $method
     * @param array $args
     * @return Model
     */
    protected function queryBuilder($method = null, array $args = [])
    {
        if ($this->foreignKeyValues) {
            $qb = $this->class->whereIn($this->localKey, $this->foreignKeyValues);
        } else {
            $qb = $this->class->where($this->localKey, '=', $this->parentClass->{$this->foreignKey});
        }

        return $qb;
    }

    /**
     * @param array $values
     * @return Model
     */
    protected function queryBuilderIn($values = [])
    {
        $qb = $this->class->whereIn($this->localKey, $values);

        return $qb;
    }

    protected function getLocalKey()
    {
        return $this->localKey;
    }

    /**
     * @return Model
     */
    protected function queryBuilderHas()
    {
        return $this->class->where(
            $this->class->getTable() . '.' . $this->localKey,
            '=',
            DB::raw($this->parentClass->getTable() . '.' . $this->foreignKey)
        );
    }

    public function save(Model $model)
    {
        $model->{$this->localKey} = $this->parentClass->{$this->foreignKey};
        $model->save();
    }
}