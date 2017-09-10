<?php

namespace Spirit\Structure\Model\Relations;

use Spirit\DB;
use Spirit\Structure\Model;

class BelongToMany extends Relation
{
    static $isType = self::IS_ARRAY;

    protected $linkTable;
    protected $linkTableParentKey;
    protected $linkTableChildKey;
    protected $parentKey;
    protected $childKey;

    public function setLinkTable($_linkTable)
    {
        $this->linkTable = $_linkTable;

        return $this;
    }

    public function setLinkTableParentKey($_linkTableParentKey)
    {
        $this->linkTableParentKey = $_linkTableParentKey;

        return $this;
    }

    /**
     * @param $_linkTableChildKey
     * @return $this
     */
    public function setLinkTableChildKey($_linkTableChildKey)
    {
        $this->linkTableChildKey = $_linkTableChildKey;

        return $this;
    }

    public function setParentKey($_parentKey)
    {
        $this->parentKey = $_parentKey;

        return $this;
    }

    public function setChildKey($_childKey)
    {
        $this->childKey = $_childKey;

        return $this;
    }

    /**
     * @param $method
     * @param array $args
     * @return Model
     */
    protected function queryBuilder($method = null, array $args = [])
    {
        $qb = $this->class->join($this->linkTable . ' as pivot', $this->linkTableChildKey, ($this->childKey ?
            $this->childKey : 'id'));

        if ($this->foreignKeyValues) {
            $qb->whereIn('pivot.' . $this->linkTableParentKey, $this->foreignKeyValues);
        } else {
            $qb->where('pivot.' . $this->linkTableParentKey, '=', $this->parentClass->{$this->parentKey});
        }

        return $qb;
    }

    /**
     * @param array $values
     * @return Model
     */
    protected function queryBuilderIn($values = [])
    {
        $qb = $this->class
            ->join($this->linkTable . ' as pivot', $this->linkTableChildKey, ($this->childKey ? $this->childKey : 'id'));

        $qb->whereIn('pivot.' . $this->linkTableParentKey, $values);

        return $qb;
    }

    protected function getLocalKey()
    {
        return $this->linkTableParentKey;
    }

    /**
     * @return Model
     */
    protected function queryBuilderHas()
    {
        $qb = $this->class->join($this->linkTable . ' as pivot', $this->linkTableChildKey, ($this->childKey ? $this->childKey : 'id'));

        return $qb->where(
            'pivot.' . $this->linkTableParentKey,
            '=',
            DB::raw($this->parentClass->getTable() . '.' . $this->parentKey)
        );
    }

    public function save(Model $model, $attributes = [])
    {
        $model->save();

        $attributes[$this->linkTableChildKey] = $model->{($this->childKey ? $this->childKey : 'id')};
        $attributes[$this->linkTableParentKey] = $this->parentClass->{$this->parentKey};

        $this->parentClass->getConnection()
            ->table($this->linkTable)
            ->insert($attributes);
    }

    public function detach($ids)
    {
        $this->parentClass->getConnection()
            ->table($this->linkTable)
            ->where($this->linkTableParentKey, $this->parentClass->{$this->parentKey})
            ->whereNotIn($this->linkTableChildKey, $ids)
            ->delete();
    }

    public function attach($id, $attributes = null)
    {
        if (!is_array($id)) {
            if (is_null($attributes)) {
                $ids =[$id];
            } else {
                $ids =[$id => $attributes];
            }
        } else {
            $ids = $id;
        }

        $this->sync($ids, false);
    }

    public function sync($ids = [], $detaching = true)
    {
        $update = [];
        $insert = [];
        foreach ($ids as $id => $attributes) {
            if (!is_array($attributes)) {
                list($id, $attributes) = [$attributes, []];
            }

            if (count($attributes)) {
                foreach ($attributes as $k => $v) {
                    if (in_array($k, $update)) continue;

                    $update[] = $k;
                }

            }

            $attributes[$this->linkTableChildKey] = $id;
            $attributes[$this->linkTableParentKey] = $this->parentClass->{$this->parentKey};

            $insert[$id] = $attributes;
        }

        $this->parentClass->getConnection()
            ->insertOrUpdate($this->linkTable)
            ->columns(array_keys($insert))
            ->insert($insert)
            ->update($update)
            ->unique($this->linkTableParentKey, $this->linkTableChildKey)
            ->exec()
        ;

        if ($detaching) {
            $this->parentClass->getConnection()
                ->table($this->linkTable)
                ->where($this->linkTableParentKey, $this->parentClass->{$this->parentKey})
                ->whereNotIn($this->linkTableChildKey, array_keys($insert))
                ->delete();
        }
    }

    public function syncWithoutDetaching($ids)
    {
        $this->sync($ids, false);
    }
}