<?php

namespace Spirit\Structure\Model;

use Spirit\DB\Builder;
use Spirit\Structure\Model;
use Spirit\Structure\Model\Relations\BelongTo;
use Spirit\Structure\Model\Relations\BelongToMany;
use Spirit\Structure\Model\Relations\HasMany;
use Spirit\Structure\Model\Relations\HasOne;
use Spirit\Structure\Model\Relations\Relation;

/**
 * Class HasTrait
 * @package Spirit\Structure\Model
 *
 * @property string $table
 * @property string $primaryKey
 * @method Builder getQueryBuilder
 * @mixin Model
 */
trait RelationTrait
{

    /**
     * @var Relation[]
     */
    protected $relations = [];
    protected $relationLoaded = [];

    /**
     * @return Model
     */
    protected function withRelation()
    {
        $_relations = func_get_args();

        if (count($_relations) == 1 && is_array($_relations[0])) {
            $_relations = $_relations[0];

        } elseif (count($_relations) == 2 && is_string($_relations[0]) && is_callable($_relations[1])) {
            $_relations = [$_relations[0] => $_relations[1]];
        }

        foreach ($_relations as $key => $func) {
            if (!is_callable($func)) {
                list($key, $func) = [$func, null];
            }

            $cArr = explode('.', $key, 2);

            /**
             * @var Relation $hasClass
             */
            if (!isset($this->relations[$cArr[0]])) {
                $this->relations[$cArr[0]] = $hasClass = $this->{$cArr[0]}();
                $hasClass->setName($cArr[0]);
            } else {
                $hasClass = $this->relations[$cArr[0]];
            }

            if (isset($cArr[1])) {
                $hasClass->with($func ? [$cArr[1] => $func] : $cArr[1]);
            } else if ($func) {
                $hasClass->setCustomWhere($func);
            }
        }

        return $this;
    }

    /**
     * @see Model::getQueryBuilder()
     * @param $relation
     * @param null $operatorOrFunc
     * @param null $amount
     * @return $this
     */
    protected function whereHasRelation($relation, $operatorOrFunc = null, $amount = null)
    {
        $relationArr = explode('.', $relation, 2);

        /**
         * @var Relation $hasClass
         */
        $hasClass = $this->{$relationArr[0]}();
        $queryHas = $hasClass->queryHas();

        if (isset($relationArr[2])) {
            $queryHas->whereHas($relationArr[2], $operatorOrFunc, $amount);
            $operatorOrFunc = null;
            $amount = null;
        }

        if (!is_null($amount)) {
            $this->getQueryBuilder()->whereRaw(
                '(' . $queryHas->countSql() . ') ' . $operatorOrFunc . ' ' . $amount
            );
        } else {
            if (!is_null($operatorOrFunc) && is_callable($operatorOrFunc)) {
                $operatorOrFunc($queryHas);
            }

            $this->getQueryBuilder()->exist($queryHas->getSql());
        }

        return $this;
    }

    public function setHasLoad($field, $result)
    {
        $this->relationLoaded[$field] = $result;
    }

    protected function getForeignKey()
    {
        $table = $this->table;

        if ($table[strlen($table) - 2] === 'es') {
            $table = substr($table, 0, -2);
        } elseif ($table[strlen($table) - 1] === 's') {
            $table = substr($table, 0, -1);
        }

        return $table . '_' . $this->primaryKey;
    }

    protected function getLocalKey()
    {
        return $this->primaryKey;
    }

    /**
     * @param $className
     * @param null $local_key
     * @param null $foreign_key
     * @return HasOne
     */
    protected function hasOne($className, $local_key = null, $foreign_key = null)
    {
        $foreign_key = $foreign_key ? $foreign_key : $this->getForeignKey();
        $local_key = $local_key ? $local_key : $this->getLocalKey();

        /**
         * @var Model $this
         * @var Model $class
         */
        $class = new $className();

        return HasOne::make($this, $class)
            ->setForeignKey($foreign_key)
            ->setLocalKey($local_key);
    }

    /**
     * @param $className
     * @param bool $local_key
     * @param bool $foreign_key
     * @return BelongTo
     */
    protected function belongTo($className, $local_key = false, $foreign_key = false)
    {
        $foreign_key = $foreign_key ? $foreign_key : $this->getForeignKey();
        $local_key = $local_key ? $local_key : $this->getLocalKey();

        /**
         * @var Model $this
         * @var Model $class
         */
        $class = new $className();

        return BelongTo::make($this, $class)
            ->setForeignKey($foreign_key)
            ->setLocalKey($local_key);
    }

    /**
     * @param $className
     * @param $link_table
     * @param bool $link_table_parent_key
     * @param bool $link_table_child_key
     * @param bool $table_parent_key
     * @param bool $table_child_key
     * @return BelongToMany
     */
    protected function belongToMany($className, $link_table, $link_table_parent_key = false, $link_table_child_key = false, $table_parent_key = false, $table_child_key = false)
    {
        // (Child::class,parent_child,parent_id,child_id,id,id)

        $link_table_parent_key = $link_table_parent_key ? $link_table_parent_key : $this->getForeignKey();
        $table_parent_key = $table_parent_key ? $table_parent_key : $this->getLocalKey();

        /**
         * @var Model $this
         * @var Model $class
         */
        $class = new $className();

        return BelongToMany::make($this, $class)
            ->setLinkTable($link_table)
            ->setLinkTableParentKey($link_table_parent_key)
            ->setLinkTableChildKey($link_table_child_key)
            ->setChildKey($table_child_key)
            ->setParentKey($table_parent_key);
    }

    /**
     * @param $className
     * @param bool $local_key
     * @param bool $foreign_key
     * @return HasMany
     */
    protected function hasMany($className, $local_key = false, $foreign_key = false)
    {
        $foreign_key = $foreign_key ? $foreign_key : $this->getForeignKey();
        $local_key = $local_key ? $local_key : $this->getLocalKey();

        /**
         * @var Model $this
         * @var Model $class
         */
        $class = new $className();

        return HasMany::make($this, $class)
            ->setForeignKey($foreign_key)
            ->setLocalKey($local_key);
    }

}