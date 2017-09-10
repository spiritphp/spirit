<?php

namespace Spirit\DB\Schema;

class Foreign
{

    public static function make(Table $table, $fieldName)
    {
        return new Foreign($table, $fieldName);
    }

    protected $fieldName;

    /**
     * @var Table
     */
    protected $table;
    protected $referenceTableName;
    protected $referenceFieldName = 'id';

    protected $onUpdate = 'CASCADE';
    protected $onDelete = 'CASCADE';

    public function __construct(Table $table, $fieldName)
    {
        $this->fieldName = $fieldName;
        $this->table = $table;
    }

    /**
     * @param $fieldName
     * @param bool|false $tableName
     * @return $this
     */
    public function references($fieldName, $tableName = false)
    {
        if ($tableName) {
            $this->referenceTableName = $tableName;
        }

        $this->referenceFieldName = $fieldName;

        return $this;
    }

    /**
     * @param $tableName
     * @return Foreign
     */
    public function on($tableName)
    {
        $this->referenceTableName = $tableName;

        /**
         * @var Foreign $this
         */
        return $this;
    }

    public function onDelete($type = 'CASCADE')
    {
        $this->onDelete = $type;

        return $this;
    }

    public function onUpdate($type = 'CASCADE')
    {
        $this->onUpdate = $type;

        return $this;
    }

    public function getSql()
    {
        $key = $this->table->getName() . '__' . $this->fieldName . '__fkey';

        $sql =
            'ALTER TABLE ' . $this->table->getName() .
            ' ADD CONSTRAINT ' . $key .
            ' FOREIGN KEY (' . $this->fieldName . ')' .
            ' REFERENCES ' . $this->referenceTableName . ' (' . $this->referenceFieldName . ')' .
            ' ON UPDATE ' . $this->onUpdate . ' ON DELETE ' . $this->onDelete;

        return $sql;
    }
}