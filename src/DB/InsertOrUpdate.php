<?php

namespace Spirit\DB;

use Spirit\DB;
use Spirit\Func\Arr;

class InsertOrUpdate
{

    public static function make(Connection $connection)
    {
        return new static($connection);
    }

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var string
     */
    protected $table;

    protected $set = [];
    protected $rows = [];
    protected $columns = [];
    protected $uniqueColumns = [];

    protected $bindParams = [];

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param $table_name
     * @return $this
     */
    public function table($table_name)
    {
        $this->table = $table_name;

        return $this;
    }

    /**
     * @param array ...$v
     * @return $this
     */
    public function columns(...$v)
    {
        if (is_array($v[0])) {
            $v = $v[0];
        }

        $this->columns = $v;

        return $this;
    }

    /**
     * Only for PostgreSql
     * @param array ...$v
     * @return $this
     */
    public function unique(...$v)
    {
        if (is_array($v[0])) {
            $v = $v[0];
        }

        $this->uniqueColumns = array_merge($this->uniqueColumns, $v);

        return $this;
    }

    /**
     * @param array $rows
     * @return $this
     */
    public function insert($rows)
    {
        if (Arr::isArrayInArray($rows)) {
            $this->rows = array_merge($this->rows, $rows);
        } else {
            $this->rows[] = $rows;
        }

        return $this;
    }

    /**
     * @param array $set
     * @return $this
     */
    public function update($set)
    {
        $this->set = $set;

        return $this;
    }

    public function getSql()
    {
        $sql = [];

        $sql[] = 'INSERT INTO ' . $this->table;

        if (count($this->columns) == 0) {
            $row = $this->rows[0];

            foreach($row as $k => $v) {
                $this->columns[] = $k;
            }
        }

        $sql[] = '(' . implode(', ', $this->columns) . ')';

        $sql[] = 'VALUES';

        $inserts = [];
        foreach($this->rows as $row) {
            $insert = [];

            foreach($row as $column => $value) {
                if (is_object($value) && $value instanceof Raw) {
                    $insert[] = (string)$value;
                } else {
                    $insert[] = '?';
                    $this->bindParams[] = $value;
                }
            }

            $inserts[] = '(' . implode(', ', $insert) . ')';
        }

        $sql[] = implode(', ', $inserts);

        if ($this->connection->isDriver(DB::DRIVER_MYSQL)) {
            $sql[] = 'ON DUPLICATE KEY UPDATE';
        } else {
            $sql[] = 'ON CONFLICT (' . implode(', ', $this->uniqueColumns) . ') DO UPDATE SET';
        }

        foreach($this->set as $k => $v) {
            if (!is_string($k)) {
                if (is_object($v) && $v instanceof Raw) {
                    $sql[] = (string)$v;
                } else {
                    if ($this->connection->isDriver(DB::DRIVER_MYSQL)) {
                        $sql[] = $v . ' = VALUES(' . $v . ')';
                    } else {
                        $sql[] = $v . '= EXCLUDED.' . $v;
                    }
                }
            } else {
                if (is_object($v) && $v instanceof Raw) {
                    $sql[] = $k . ' = ' . (string)$v;
                } else {
                    $sql[] = $k . ' = ?';
                    $this->bindParams[] = $v;
                }
            }
        }

        if ($this->connection->isDriver(DB::DRIVER_POSTGRESQL)) {
            $sql[] = 'RETURNING id';
        }

        return implode("\n ", $sql);
    }

    public function getBindParams()
    {
        return $this->bindParams;
    }

    /**
     * @return null|\PDOStatement
     */
    public function exec()
    {
        $stmt = $this->connection->execute($this->getSql(),$this->getBindParams());

        return $stmt;
    }
}