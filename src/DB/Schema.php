<?php

namespace Spirit\DB;

use Spirit\DB;
use Spirit\DB\Schema\Table;

/**
 * Class Schema
 * @package Spirit\DB
 *
 * @method static void create(string $tableName, \Closure $callback)
 * @method static void drop(string $tableName)
 * @method static void table(string $tableName, \Closure $callback)
 * @method static bool hasTable(string $tableName)
 * @method static bool hasColumn(string $tableName, string $columnName)
 */
class Schema {

    public static function make($connection_name = null)
    {
        return new static($connection_name);
    }

    /**
     * @var Connection
     */
    protected $connection;

    public function __construct($connection_name = null)
    {
        $this->connection = DB::connect($connection_name);
    }


    public function __call($method, array $args = [])
    {
        $method = '__' . $method;

        return call_user_func_array([$this, $method], $args);
    }

    /**
     * @param $method
     * @param array $args
     * @return mixed
     */
    public static function __callStatic($method, array $args = [])
    {
        $class = static::make();

        return call_user_func_array([$class, $method], $args);
    }

    /**
     * @param $nameTable
     * @param \Closure $callback
     */
    protected function __create($nameTable, \Closure $callback)
    {
        Table::make($this->connection)
            ->name($nameTable)
            ->command($callback)
            ->execute();
    }

    protected function __table($nameTable, \Closure $callback)
    {
        Table::make($this->connection)
            ->name($nameTable)
            ->exist()
            ->command($callback)
            ->execute();
    }

    protected function __drop($nameTable)
    {
        Table::make($this->connection)
            ->name($nameTable)
            ->exist()
            ->command(function(Table $table) {
                $table->drop();
            })
            ->execute()
        ;
    }

    protected function __hasTable($tableName)
    {
        return $this->connection->hasTable($tableName);
    }

    protected function __hasColumn($tableName, $columnName)
    {
        return $this->connection->hasColumn($tableName, $columnName);
    }

}