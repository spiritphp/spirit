<?php

namespace Spirit\Structure\Model;

use Spirit\DB;
use Spirit\Structure\Model;

/**
 * Trait HelperTrait
 * @package Spirit\Structure\Model
 *
 * @mixin Model
 */
trait HelperTrait {

    public function reload()
    {
        return static::find($this->id);
    }

    public function getId()
    {
        if (!isset($this->data[$this->primaryKey])) return null;

        return $this->data[$this->primaryKey];
    }

    public function getTable()
    {
        return $this->table;
    }

    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * @return null|DB\Connection
     */
    public function getConnection()
    {
        return DB::connect($this->connection);
    }

}