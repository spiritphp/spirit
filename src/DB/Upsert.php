<?php

namespace Spirit\DB;

use Spirit\DB;
use Spirit\Func;

class Upsert
{
    public static function make(Connection $connection)
    {
        return new static($connection);
    }

    public static function getTableNewValues()
    {
        return static::$tableNewValues;
    }

    /**
     * @var Connection
     */
    protected $connection;

    protected static $tableNewValues = 'new_values';
    protected $table;
    protected $data = [];
    protected $unique = [];
    protected $update = [];
    protected $uniqueWhere = [];

    protected $bindParams = [];

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function table($table)
    {
        $this->table = $table;
        return $this;
    }

    public function data($data)
    {
        $this->data = $data;
        return $this;
    }

    public function update($key, $value = false)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                if (!is_array($v)) {
                    list($k, $v) = [$v, []];
                }

                $this->update[$k] = $v;
            }
        } else {
            $this->update[$key] = $value;
        }

        return $this;
    }

    public function unique()
    {
        $fields = func_get_args();
        if (count($fields) == 1) {
            if (is_array($fields[0])) {
                $this->unique = array_merge($this->unique, $fields[0]);
            } else {
                $this->unique[] = $fields[0];
            }
        } else {
            $this->unique = array_merge($this->unique, $fields);
        }

        return $this;
    }

    public function uniqueWhere($w)
    {
        if (is_array($w)) {
            $this->uniqueWhere = array_merge($this->uniqueWhere, $w);
        } else {
            $this->uniqueWhere[] = $w;
        }

        return $this;
    }

    protected function upsertUpdate()
    {
        $sqlArr = [];
        $sqlArr[] = "\tUPDATE";
        $sqlArr[] = "\t\t" . $this->table;
        $sqlArr[] = "\tSET";
        $update = [];
        foreach ($this->update as $k => $v) {

            if (is_object($v) && $v instanceof Raw) {
                $value = (string)$v;
            } elseif($v) {
                $value = '?';
                $this->bindParams[] = $v;
            } else {
                $value = static::getTableNewValues() . '.' . $k;
            }

            $update[] = "\t\t" . $k . ' = ' . $value;
        }
        $sqlArr[] = implode(",\n", $update);
        $sqlArr[] = "\tFROM";
        $sqlArr[] = "\t\t" . static::getTableNewValues();
        $sqlArr[] = "\tWHERE";
        $sqlArr[] = "\t\t" . $this->whereUpdate();
        $sqlArr[] = "\tRETURNING " . $this->table . ".*";

        return implode("\n", $sqlArr);
    }

    protected function upsertSelect()
    {
        $sqlArr = [];
        $sqlArr[] = "\tSELECT";
        $sqlArr[] = "\t\t" . $this->table . ".*";
        $sqlArr[] = "\tFROM";
        $sqlArr[] = "\t\t" . $this->table . ",";
        $sqlArr[] = "\t\t" . static::getTableNewValues();
        $sqlArr[] = "\tWHERE";
        $sqlArr[] = "\t\t" . $this->whereUpdate();

        return implode("\n", $sqlArr);
    }

    protected function whereUpdate($table = false, $table_new = false)
    {
        $table = $table ? $table : $this->table;
        $table_new = $table_new ? $table_new : static::getTableNewValues();

        $where = [];
        if (count($this->unique)) {
            foreach ($this->unique as $f) {
                $where[] = $table . '.' . $f . ' = ' . $table_new . '.' . $f;
            }
        }

        if (count($this->uniqueWhere)) {
            foreach ($this->uniqueWhere as $w) {
                if ($table !== $this->table) {
                    $w = str_replace($this->table . '.', $table . '.', $w);
                }

                if ($table_new !== static::getTableNewValues()) {
                    $w = str_replace(static::getTableNewValues() . '.', $table_new . '.', $w);
                }

                $where[] = $w;
            }
        }

        return implode(" AND\n", $where);
    }

    protected function getQuerySql()
    {
        $data = $this->data;
        if (!Func\Arr::isArrayInArray($data)) {
            $data = [$data];
        }

        $fields = [];
        foreach ($data as $d) {
            foreach ($d as $key => $value) {
                $fields[$key] = $key;
            }
        }

        $filledData = [];
        foreach ($data as $d) {
            $__d = [];
            foreach ($fields as $field) {
                if (!isset($d[$field])) {
                    $v = DB::raw('NULL');
                } else {
                    $v = $d[$field];
                }

                if (is_object($v) && $v instanceof Raw) {
                    $__d[$field] = (string)$v;
                } else {
                    $__d[$field] = '?';
                    $this->bindParams[] = $d[$field];
                }

            }
            $filledData[] = $__d;
        }

        $sqlArr = [];

        $sqlArr[] = "WITH";
        $sqlArr[] = "\n" . static::getTableNewValues();
        $sqlArr[] = "\t\t(" . implode(',', $fields) . ")  as (";
        $sqlArr[] = "\nVALUES";

        $insert = [];
        foreach ($filledData as $d) {
            $insert[] = "\t(" . implode(',', $d) . ")";
        }

        $sqlArr[] = implode(",\n", $insert);
        $sqlArr[] = "),";

        $sqlArr[] = "upsert as";
        $sqlArr[] = "(";

        if (count($this->update)) {
            $sqlArr[] = $this->upsertUpdate();
        } else {
            $sqlArr[] = $this->upsertSelect();
        }

        $sqlArr[] = ")";

        $sqlArr[] = "INSERT INTO";
        $sqlArr[] = "\t" . $this->table;
        $sqlArr[] = "\t\t(" . implode(', ', $fields) . ")";
        $sqlArr[] = "SELECT";
        $sqlArr[] = "\t" . implode(",\n\t", $fields);
        $sqlArr[] = "FROM";
        $sqlArr[] = "\n" . static::getTableNewValues();
        $sqlArr[] = "WHERE";
        $sqlArr[] = "\tNOT EXISTS (";
        $sqlArr[] = "\t\tSELECT 1";
        $sqlArr[] = "\t\tFROM upsert";
        $sqlArr[] = "\t\tWHERE " . $this->whereUpdate('upsert');
        $sqlArr[] = "\t)";
        $sqlArr[] = ";";

        return implode("\n", $sqlArr);
    }

    /**
     * @return null|\PDOStatement
     */
    public function exec()
    {
        $stmt = $this->connection->execute($this->getQuerySql(), $this->bindParams);

        return $stmt;
    }

    public function getSql()
    {
        return $this->getQuerySql();
    }
}