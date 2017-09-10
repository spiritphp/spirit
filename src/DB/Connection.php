<?php

namespace Spirit\DB;

use \PDO;
use Spirit\Engine;
use Spirit\Error;

abstract class Connection
{

    use Timer;

    protected $driver = null;

    protected $database;
    protected $host;
    protected $user;
    protected $password;
    protected $port;
    protected $type;

    /**
     * @var PDO
     */
    protected $connection;

    protected $allQueries = [];

    protected $currentQuery = null;
    protected $currentBindParams = null;
    protected $currentStmt = null;

    public static function make($database)
    {
        return new static($database);
    }

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function setHost($v)
    {
        $this->host = $v;
        return $this;
    }

    public function setUser($v)
    {
        $this->user = $v;
        return $this;
    }

    public function setPassword($v)
    {
        $this->password = $v;
        return $this;
    }

    public function setPort($v)
    {
        $this->port = $v;
        return $this;
    }

    public function setType($v)
    {
        $this->type = $v;
        return $this;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function connect()
    {
        try {
            $this->tStart();

            $this->connection = $this->makeConnect();

            $t = $this->tFinish();

            $this->logQuery('CONNECT DB', $t);

            if (!$this->connection) throw new \Exception('Error 0001');

        } catch (\PDOException $e) {
            $this->errorFire('0001', 'ERROR CONNECT: ' . $e->getMessage());
            exit();
        }

        return $this;
    }

    /**
     * @return PDO
     */
    protected function makeConnect()
    {
        $opt = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ];


        $pdo = new PDO(
            $this->driver .
            ':host=' . $this->host . ';' .
            'port=' . $this->port . ';' .
            'dbname=' . $this->database . ';',
            $this->user,
            $this->password
            , $opt
        );

        return $pdo;
    }

    protected function getFileAndLineQuery()
    {
        $d = debug_backtrace();
        $file = '--';
        $line = '--';
        $count = count($d);
        for ($i = 1; $i < $count; ++$i) {
            if (!isset($d[$i]['file'])) continue;

            if (
                strpos($d[$i]['file'], 'DB') === false &&
//                    strpos($d[$i]['file'], 'Connection') === false &&
//                    strpos($d[$i]['file'], 'Builder') === false &&
                strpos($d[$i]['file'], 'Structure') === false &&
                strpos($d[$i]['file'], 'Model') === false
            ) {
                $file = str_replace(Engine::i()->abs_path, '', $d[$i]['file']);
                $line = $d[$i]['line'];
                break;
            }
        }

        return [
            $file, $line
        ];
    }

    protected function logQuery($q, $t)
    {
        $m_1 = null;
        $m_2 = null;
        $file = null;
        $line = null;

        if (isDebug() || Engine::i()->isConsole) {
            $m_1 = memory_get_peak_usage();
            $m_2 = memory_get_usage();
            list($file, $line) = $this->getFileAndLineQuery();
        }

        $this->allQueries[] = [
            'query' => $q,
            'time' => $t,
            'memory1' => $m_1,
            'memory2' => $m_2,
            'map' => $file . ':' . $line
        ];
    }

    protected function errorFire($errno, $errstr)
    {
        list($file, $line) = $this->getFileAndLineQuery();

        Error::make(500, $errno . ':' . $errstr, $file, $line);
    }

    public function close()
    {
        try {
            $this->tStart();

            if ($this->connection) {
                $this->makeClose();

                $this->connection = null;
            }

            $t = $this->tFinish();

            $this->logQuery('CLOSE CONNECT DB', $t);

        } catch (\Exception $er) {
            $this->errorFire('0004', 'ERROR CLOSE DB');
            exit();
        }
    }

    protected function makeClose() {}

    public function select($sql, $bindParams = [])
    {
        $stmt = $this->execute($sql, $bindParams);

        return $stmt->fetchAll();
    }

    /**
     * @param $sql
     * @param array $bindParams
     * @return null|\PDOStatement
     */
    public function execute($sql, $bindParams = [])
    {
        $this->currentQuery = $sql;
        $this->currentBindParams = $bindParams;

        if (!$bindParams) $bindParams = [];
        if (!is_array($bindParams)) $bindParams = [$bindParams];

        $this->tStart();

        $sqlLog = SqlRaw::make($sql, $bindParams);

        try {
            $this->currentStmt = $this->connection->prepare($sql);
            $this->bindValues($this->currentStmt, $bindParams);
            $this->currentStmt->execute();

        } catch (\Exception $e) {
            $this->errorFire('0003', $e->getMessage() . "\n" . $sqlLog);
            exit();
        }

        $t = $this->tFinish();

        $this->logQuery($sqlLog, $t);

        return $this->currentStmt;
    }

    /**
     * @param \PDOStatement $statement
     * @param $bindings
     */
    public function bindValues($statement, $bindings)
    {
        foreach ($bindings as $key => $value) {

            if ($value === false) $value = 0;

            $statement->bindValue(
                is_string($key) ? $key : $key + 1, $value,
                is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
    }

    public function exec($sql)
    {
        $this->currentQuery = $sql;
        $this->currentBindParams = [];

        $this->tStart();

        try {
            $this->connection->exec($sql);

        } catch (\Exception $e) {
            $this->errorFire('0003', $e->getMessage() . "\n" . $sql);
            exit();
        }

        $t = $this->tFinish();

        $this->logQuery($sql, $t);
    }

    public function lastInsertId($name = null)
    {
        return $this->connection->lastInsertId($name);
    }

    /**
     * @param $sql
     * @param array $bindParams
     * @param bool $returnStatement
     * @return int|null|\PDOStatement
     */
    public function insert($sql, $bindParams = [], $returnStatement = false)
    {
        $stmt = $this->execute($sql, $bindParams);

        //print_r($stmt->fetch());

        return $returnStatement ? $stmt : $stmt->rowCount();
    }

    public function update($sql, $bindParams = [])
    {
        $stmt = $this->execute($sql, $bindParams);

        return $stmt->rowCount();
    }

    public function delete($sql, $bindParams = [])
    {
        $stmt = $this->execute($sql, $bindParams);

        return $stmt->rowCount();
    }

    /**
     * @return bool
     */
    public function beginTransaction()
    {
        return $this->connection->beginTransaction();
    }

    /**
     * @return bool
     */
    public function rollback()
    {
        return $this->connection->rollBack();
    }

    public function commit()
    {
        $this->connection->commit();
    }

    /**
     * @param $table_name
     * @return bool
     */
    public function hasTable($table_name)
    {
        $stmt = $this->execute("
            SELECT
                1
            FROM
                information_schema.tables
            WHERE
                table_schema = 'public' AND
                table_name = ?
            LIMIT 1
        ", [$table_name]);

        return !!$stmt->fetch();
    }

    /**
     * @param $table_name
     * @param $column_name
     * @return bool
     */
    public function hasColumn($table_name, $column_name)
    {
        $stmt = static::execute("
            SELECT
                1
            FROM
                information_schema.columns
            WHERE
                table_schema = 'public' AND
                table_name = ? AND
                column_name = ?
            LIMIT 1
        ", [$table_name, $column_name]);

        return !!$stmt->fetch();
    }

    /**
     * @param $sql
     * @return null|\PDOStatement
     * @throws \Exception
     */
    public function query($sql)
    {
        $this->currentQuery = $sql;

        $this->tStart();

        try {
            $this->currentStmt = $this->connection->query($sql);
        } catch (\Exception $e) {
            $this->errorFire('0002', $e->getMessage());
            exit();
        }

        $t = $this->tFinish();

        $this->logQuery($sql, $t);

        return $this->currentStmt;
    }

    /**
     * @param string $table_name
     * @param callable $callback
     * @return Builder
     */
    public function table($table_name, $callback = null)
    {
        return Builder::make($this)->table($table_name, $callback);
    }

    /**
     * @param string $table_name
     * @return InsertOrUpdate
     */
    public function insertOrUpdate($table_name)
    {
        return InsertOrUpdate::make($this)->table($table_name);
    }

    /**
     * @param string $table_name
     * @return Upsert
     */
    public function upsert($table_name)
    {
        return Upsert::make($this)->table($table_name);
    }

    public function isDriver($driver)
    {
        if (is_array($driver)) {
            return in_array($this->driver, $driver, true);
        }

        return $driver === $this->driver;
    }

    public function getDriverName()
    {
        return $this->driver;
    }

    public function getAllQueries()
    {
        return $this->allQueries;
    }
}