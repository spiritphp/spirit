<?php

namespace Spirit\DB\Schema;

use Spirit\DB;

/**
 * Class Table
 * @package Spirit\DB\Schema
 *
 * @method $this string(string $name, int $length = 255)
 * @method $this text(string $name)
 * @method $this integer(string $name)
 * @method $this bigInteger(string $name)
 * @method $this boolean(string $str)
 * @method $this numeric(string $name, int $length = 10, int $accuracy = 2)
 * @method $this smallInteger(string $name)
 * @method $this inet(string $name)
 * @method $this json(string $name)
 * @method $this jsonb(string $name)
 * @method $this timestamp(string $name)
 * @method $this date(string $name)
 *
 * @method $this default(mixed $str)
 * @method $this now()
 *
 * @method $this ifExists()
 * @method $this dropColumn(mixed $str)
 * @method $this dropColumnIfExists(mixed $str)
 * @method $this dropIndex(string $str)
 * @method $this dropUnique(string $str)
 * @method $this dropPrimary(string $str)
 */
class Table
{

    /**
     * @param db\Connection $connection
     * @return static
     */
    public static function make(db\Connection $connection)
    {
        return new static($connection);
    }

    protected $isExist = false;

    /**
     * @var db\Connection
     */
    protected $connection;

    /**
     * @var \Closure
     */
    protected $command;

    protected $tableName;
    protected $serial;
    protected $lastColumn;

    /**
     * @var Column[]
     */
    protected $columns = [];

    /**
     * @var Column[]
     */
    protected $dropColumns = [];

    /**
     * @var Column[]
     */
    protected $renameColumns = [];

    /**
     * @var Index[]
     */
    protected $indexes = [];

    protected $rawSql = [];

    /**
     * @var Index[]
     */
    protected $dropIndexes = [];

    /**
     * @var Foreign[]
     */
    protected $foreigns = [];
    protected $lastForeign;

    protected $sql = [];

    /**
     * Table constructor.
     * @param db\Connection $connection
     */
    public function __construct(db\Connection $connection)
    {
        $this->connection = $connection;
    }

    public function connection()
    {
        return $this->connection;
    }

    public function isMySQL()
    {
        return $this->connection->isDriver(DB::DRIVER_MYSQL);
    }

    public function isPostgreSQL()
    {
        return $this->connection->isDriver(DB::DRIVER_POSTGRESQL);
    }

    public function name($table)
    {
        $this->tableName = $table;
        return $this;
    }

    public function exist()
    {
        $this->isExist = true;
        return $this;
    }

    /**
     * @param \Closure $callback
     * @return $this
     */
    public function command($callback)
    {
        $this->command = $callback;

        return $this;
    }

    public function __call($name, $arguments)
    {
        if (isset(Column::$options[$name])) {

            $fieldName = $arguments[0];

            $column = Column::make($this, $fieldName, $name);

            if (isset($arguments[1])) {
                $column->length($arguments[1]);
            }

            if (isset($arguments[2])) {
                $column->accuracy($arguments[2]);
            }

            $this->columns[$fieldName] = $column;

            $this->lastColumn = $fieldName;
        } elseif ($name === 'default') {
            if ($this->lastColumn) {
                $this->columns[$this->lastColumn]->setDefault($arguments[0]);
            }

        } elseif ($name === 'now') {
            if ($this->lastColumn) {
                $this->columns[$this->lastColumn]->setDefault('now()');
            }

        } elseif ($name === 'ifExists') {
            if ($this->lastColumn) {
                $this->columns[$this->lastColumn]->setCheckExists(true);
            }

        } elseif (strpos($name, 'drop') === 0) {

            $type = strtolower(str_replace('drop', '', $name));

            if (in_array($type, ['index', 'primary', 'unique'])) {
                $this->dropIndexes[] = Index::make($this, $arguments[0], $type);

            } elseif ($type === 'column' || $type === 'columnifexists') {
                if (is_array($arguments[0])) {
                    $arguments = $arguments[0];
                }

                foreach ($arguments as $column) {
                    $c = Column::make($this, $column);

                    if ($type === 'columnifexists') {
                        $c->setCheckExists();
                    }

                    $this->dropColumns[] = $c;
                }
            }
        }

        return $this;
    }

    public function notNull()
    {
        if ($this->lastColumn) {
            $this->columns[$this->lastColumn]->notNull();
        }

        return $this;
    }

    public function autoIncrement()
    {
        if ($this->lastColumn) {
            $this->columns[$this->lastColumn]->autoIncrement();
        }

        return $this;
    }

    protected function addIndex($type, $fieldName = null)
    {
        if (!$fieldName && $this->lastColumn) {
            $fieldName = $this->lastColumn;
        }

        if (!$fieldName) {
            return $this;
        }

        $this->indexes[] = Index::make($this, $fieldName, $type);

        return $this;
    }

    public function index($fieldName = null)
    {
        return $this->addIndex('index', $fieldName);
    }

    public function primary($fieldName = null)
    {
        return $this->addIndex('primary', $fieldName);
    }

    public function unique($fieldName = null)
    {
        return $this->addIndex('unique', $fieldName);
    }

    protected function increment($fieldName)
    {
        $this->notNull();

        if ($this->isMySQL()) {
            $this->autoIncrement();
        } else {
            $this->primary();
        }

        $this->serial = $fieldName;

        return $this;
    }

    public function bigIncrements($fieldName)
    {
        $this->bigInteger($fieldName);

        return $this->increment($fieldName);
    }

    public function increments($fieldName)
    {
        $this->integer($fieldName);

        return $this->increment($fieldName);
    }

    public function serial($fieldName)
    {
        return $this->increments($fieldName);
    }

    public function bigSerial($fieldName)
    {
        return $this->bigIncrements($fieldName);
    }

    public function timestamps()
    {
        $this->timestamp('created_at')->default('now()');
        $this->timestamp('updated_at');

        return $this;
    }

    public function softRemove()
    {
        $this->timestamp('removed_at');

        return $this;
    }

    public function renameColumn($oldName, $newName)
    {
        $c = Column::make($this, $oldName)->setNewName($newName);

        $this->renameColumns[] = $c;
    }

    /**
     * @param bool|false $fieldName
     * @return Foreign
     */
    public function foreign($fieldName = false)
    {
        if (!$fieldName) {
            $fieldName = $this->lastColumn;
        }

        if (!$fieldName) return null;

        $k = $this->tableName . '__' . $fieldName . '__fkey';
        return $this->foreigns[$k] = Foreign::make($this, $fieldName);
    }

    public function raw($sql)
    {
        $this->rawSql[] = $sql;

        return $this;
    }

    protected function initSQLForColumns()
    {
        if (count($this->columns)) {
            foreach ($this->columns as $name => $column) {
                if (!$column->needCheckExists() || $this->hasColumn($name)) {
                    $this->sql[] = $column->getSqlForCreate();
                }
            }
        }

        if (count($this->dropColumns)) {
            foreach ($this->dropColumns as $name => $column) {
                $this->sql[] = $column->getSqlForDrop();
            }
        }

        if (count($this->renameColumns)) {
            foreach ($this->renameColumns as $name => $column) {
                $this->sql[] = $column->getSqlForRename();
            }
        }
    }

    protected function initSQLForIndexes()
    {
        if (count($this->indexes)) {
            foreach ($this->indexes as $index) {
                $this->sql[] = $index->getSql();
            }
        }

        if (count($this->dropIndexes)) {
            foreach ($this->dropIndexes as $index) {
                $this->sql[] = $index->getSqlForDrop();
            }
        }
    }

    protected function initSQLForForeigns()
    {
        if (count($this->foreigns)) {
            foreach ($this->foreigns as $foreign) {
                $this->sql[] = $foreign->getSql();
            }
        }
    }

    protected function initSQLForSerial()
    {
        if (!$this->isPostgreSQL()) return;

        if ($this->serial) {
            $key = $this->tableName . '_' . $this->serial . '_seq';
            $this->sql[] = 'CREATE SEQUENCE ' . $key . ' START WITH 1 INCREMENT BY 1 NO MINVALUE NO MAXVALUE CACHE 1';
            $this->sql[] = 'ALTER SEQUENCE ' . $key . ' OWNED BY ' . $this->tableName . '.' . $this->serial;
            $this->sql[] =
                'ALTER TABLE ONLY ' . $this->tableName .
                ' ALTER COLUMN ' . $this->serial .
                ' SET DEFAULT nextval(\'' . $key . '\'::regclass)';
        }
    }

    public function getSql()
    {
        if (!$this->isExist) {
            $columns = [];
            foreach ($this->columns as $name => $column) {
                $columns[] = $column->getSqlForCreateTable();
            }
            $this->columns = [];

            $sql = "CREATE TABLE " . $this->tableName . " (\n\t" . implode(",\n\t", $columns) . "\n)";

            if ($this->isMySQL()) {
                $sql .= " ENGINE=InnoDB DEFAULT CHARSET=utf8";
            }

            $this->sql[] = $sql;
        }

        $this->initSQLForColumns();
        $this->initSQLForSerial();
        $this->initSQLForIndexes();
        $this->initSQLForForeigns();

        foreach ($this->rawSql as $__rawSql) {
            $this->sql[] = $__rawSql;
        }

        return implode(";\n", $this->sql) . "\n";
    }

    public function execute()
    {
        call_user_func($this->command, $this);

        $this->connection->exec($this->getSql());
    }

    public function drop()
    {
        $this->sql[] = "DROP TABLE IF EXISTS " . $this->tableName . " CASCADE";
    }

    public function hasColumn($columnName)
    {
        return $this->connection->hasColumn($this->tableName, $columnName);
    }

    public function getName()
    {
        return $this->tableName;
    }

    public function isHas($isHas = true)
    {
        $this->isExist = $isHas;

        return $this;
    }
}