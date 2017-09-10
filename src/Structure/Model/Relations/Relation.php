<?php

namespace Spirit\Structure\Model\Relations;

use Spirit\DB\Builder;
use Spirit\Structure\Model;

/**
 * Class Relation
 * @package Spirit\Structure\Model\Has
 *
 *
 *
 * @method static Builder|Model with(...$param)
 * @method static Builder|Model where(...$param)
 * @method static Builder|Model whereIn(...$param)
 * @method static Builder|Model orderBy(...$param)
 * @method static Builder|Model join(...$param)
 */
abstract class Relation
{
    const IS_ARRAY = 'array';
    const IS_ONE = 'one';

    protected static $isType = self::IS_ONE;

    /**
     * @var Model
     */
    protected $parentClass;

    /**
     * @var Model
     */
    protected $class;

    protected $name;
    protected $foreignKeyValues;
    protected $relations;
    protected $customWhere;

    public static function make(Model $parentClass, Model $class)
    {
        return new static($parentClass, $class);
    }

    public function __construct(Model $parentClass, Model $class)
    {
        $this->parentClass = $parentClass;
        $this->class = $class;
    }

    public function setForeignKeyValues(array $value)
    {
        $this->foreignKeyValues = $value;

        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function setCustomWhere($where)
    {
        $this->customWhere = $where;

        return $this;
    }

    public function __call($method, array $args = [])
    {
        $qb = $this->queryBuilder($method, $args);

        return $qb->{$method}(...$args);
    }

    /**
     * Связующий запрос
     * @param $method
     * @param array $args
     * @return Model
     */
    abstract protected function queryBuilder($method = null, array $args = []);

    /**
     * @param array $values
     * @return Model
     */
    abstract protected function queryBuilderIn($values = []);

    abstract protected function getLocalKey();

    /**
     * @return Model
     */
    abstract protected function queryBuilderHas();


    protected function fetchWhereIn($values = [])
    {
        $q = $this->queryBuilderIn($values);

        if ($this->customWhere) {
            $callback = $this->customWhere;
            $callback($q->getQueryBuilder());
        }

        return $q->get();
    }

    public function getWhereIn($values = [])
    {
        $items = $this->fetchWhereIn($values);

        $localKey = $this->getLocalKey();

        $result = [];
        foreach ($items as $item) {
            $key = $item->$localKey;

            if (static::$isType === static::IS_ONE) {
                $result[$key] = $item;
            } else {
                if (!isset($result[$key])) {
                    $result[$key] = [];
                }
                $result[$key][] = $item;
            }

        }

        return $result;
    }

    /**
     * @return \Spirit\Collection|Model
     */
    public function get()
    {
        $q = $this->queryBuilder();

        if (static::$isType === static::IS_ONE) {
            return $q->first();
        }

        return $q->get();
    }

    /**
     * Для запросов whereHas
     * @return Model
     */
    public function queryHas()
    {
        return $this->queryBuilderHas();
    }

    public function getName()
    {
        return $this->name;
    }

    public function emptyValue()
    {
        if (static::$isType === static::IS_ONE) {
            return null;
        }

        return [];
    }

    /**
     * Удаляем все привязанные модели модели
     * @return int
     */
    public function forceDelete()
    {
        return $this->queryBuilder()->delete();
    }
}