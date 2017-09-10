<?php

namespace Spirit\DB\Schema;

class Index
{

    public static function make(Table $table, $field, $type = null)
    {
        return new Index($table, $field, $type);
    }

    /**
     * @var Table
     */
    protected $table;

    protected $field;

    protected $type;

    public function __construct(Table $table, $field, $type = null)
    {
        if (is_string($field)) {
            $field = [$field];
        }

        $this->field = $field;
        $this->table = $table;

        if ($type) {
            $this->type = $type;
        }
    }

    public function unique()
    {
        $this->type = 'unique';
    }

    public function index()
    {
        $this->type = 'index';
    }

    public function primary()
    {
        $this->type = 'primary';
    }

    public function getKey()
    {
        return $this->table->getName() . '__' . implode('_', $this->field) . '__' . $this->type;
    }

    public function getSql()
    {
        if ($this->type === 'primary') {
            $sql =
                'ALTER TABLE ONLY ' . $this->table->getName() .
                ' ADD CONSTRAINT ' . $this->getKey() .
                ' PRIMARY KEY (' . implode(', ', $this->field) . ')';
        } else {
            $sql =
                'CREATE' . ($this->type === 'unique' ? ' UNIQUE' : '') .
                ' INDEX ' . $this->getKey() .
                ' ON ' . $this->table->getName() .
                ($this->table->isPostgreSQL() ? ' USING btree' : '') .
                ' (' . implode(', ', $this->field) . ')';
        }

        return $sql;
    }

    public function getSqlForDrop()
    {
        if ($this->type === 'primary') {
            $sql =
                'ALTER TABLE ' . $this->table->getName() .
                ' DROP CONSTRAINT ' . $this->getKey();
        } else {
            $sql =
                'DROP INDEX ' . $this->getKey();
        }

        return $sql;
    }
}