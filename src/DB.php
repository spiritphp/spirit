<?php

namespace Spirit;

use Spirit\DB\Builder;
use Spirit\DB\Connection;
use Spirit\DB\InsertOrUpdate;
use Spirit\DB\MySQL;
use Spirit\DB\PostgreSQL;
use Spirit\DB\Raw;
use Spirit\DB\Upsert;

/**
 * Class DB
 * @package Spirit
 *
 * @method static \PDOStatement query($sql)
 * @method static \PDOStatement select($sql, array $bindParams = [])
 * @method static void insert($sql, array $bindParams = [])
 * @method static integer update($sql, array $bindParams = [])
 * @method static integer delete($sql, array $bindParams = [])
 * @method static \PDOStatement execute($sql, array $bindParams = [])
 * @method static Builder table(string $table_name, callable $callback = null)
 * @method static InsertOrUpdate insertOrUpdate($table_name)
 * @method static Upsert upsert($table_name)
 * @method static bool beginTransaction()
 * @method static bool rollback()
 * @method static void commit()
 * @method static void exec($sql)
 * @method static bool hasColumn($table_name, $column_name)
 * @method static bool hasTable($table_name)
 * @method static bool isDriver(array|string $table_name)
 * @method static string getDriverName()
 *
 */
class DB
{

    const DRIVER_POSTGRESQL = 'pgsql';
    const DRIVER_MYSQL = 'mysql';

    /**
     * @var Connection[]
     */
    protected static $drivers = [
        self::DRIVER_POSTGRESQL => PostgreSQL::class,
        self::DRIVER_MYSQL => MySQL::class,
    ];

    /**
     * @var Connection[]
     */
    protected static $connections = [];

    protected static $cfg = [];
    protected static $defaultConnection;

    public static function setCfg($cfg, $default = 'pgsql')
    {
        static::$cfg = $cfg;
        static::$defaultConnection = $default;
    }

    /**
     *
     * @param null $connectionName
     * @return Connection|null
     * @throws \Exception
     */
    public static function connect($connectionName = null)
    {
        if (is_null($connectionName)) {
            $connectionName = static::$defaultConnection;
        }

        if (isset(static::$connections[$connectionName])) {
            return static::$connections[$connectionName];
        }

        if (!isset(static::$cfg[$connectionName])) {
            throw new \Exception('Connection «' . $connectionName . '» is not found');
        }

        $opt = static::$cfg[$connectionName];

        $className = static::$drivers[$opt['driver']];
        $connection = $className::make($opt['database']);

        if (isset($opt['type'])) {
            $connection->setType($opt['type']);
        }

        $connection->setHost($opt['host'])
            ->setUser($opt['user'])
            ->setPassword($opt['password'])
            ->setPort($opt['port']);

        return static::$connections[$connectionName] = $connection->connect();
    }

    public static function getConnections()
    {
        return static::$connections;
    }

    /**
     * @param $method
     * @param array $args
     * @return mixed
     */
    public static function __callStatic($method, array $args = [])
    {
        $class = static::connect();

        return $class->{$method}(...$args);
    }

    public static function raw($v)
    {
        return Raw::make($v);
    }

    public static function getAllQueries()
    {
        $queries = [];

        foreach (static::$connections as $connection) {
            $queries = array_merge($queries, $connection->getAllQueries());
        }

        return $queries;
    }
}