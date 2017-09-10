<?php

namespace Spirit\DB;

use Spirit\Collection\Paginate;
use Spirit\Collection;
use Spirit\DB;
use Spirit\DB\Builder\Having;
use Spirit\DB\Builder\Join;
use Spirit\DB\Builder\Where;

/**
 * Class Builder
 * @package Spirit\DB
 */
class Builder
{

    const TYPE_COUNT = 'count';
    const TYPE_INSERT = 'insert';

    protected $callback;
    protected $table;
    protected $fields;
    protected $limit;
    protected $pass;
    protected $orders;
    protected $groups;


    protected $primaryKey = 'id';

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var Where
     */
    protected $whereInstance;

    /**
     * @var Join
     */
    protected $joinInstance;

    /**
     * @var Having
     */
    protected $havingInstance;

    protected $bindParams = [];


    /**
     * @param Connection $connection
     * @return static|Builder
     */
    public static function make(Connection $connection)
    {
        return new static($connection);
    }

    /**
     * Builder constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }


    /**
     * @return Where
     */
    public function getWhere()
    {
        if (!$this->whereInstance) {
            $this->whereInstance = new Where();
        }

        return $this->whereInstance;
    }

    /**
     * @return Join
     */
    public function getJoin()
    {
        if (!$this->joinInstance) {
            $this->joinInstance = new Join();
        }

        return $this->joinInstance;
    }

    /**
     * @return Having
     */
    public function getHaving()
    {
        if (!$this->havingInstance) {
            $this->havingInstance = new Having();
        }

        return $this->havingInstance;
    }

    /**
     * @param $table
     * @param $callback null|mixed
     * @return $this
     */
    public function table($table, $callback = null)
    {
        $this->table = $table;
        $this->callback = $callback;

        return $this;
    }

    public function withPrimaryKey($id)
    {
        $this->primaryKey = $id;

        return $this;
    }

    public function select()
    {
        $fields = func_get_args();

        if (count($fields) == 1) $fields = $fields[0];

        $this->fields = $fields;

        return $this;
    }

    public function where($field, $operator = '=', $value = '')
    {
        $this->getWhere()->where($field, $operator, $value);

        return $this;
    }

    public function orWhere($field, $operator = '=', $value = '')
    {
        $this->getWhere()->orWhere($field, $operator, $value);

        return $this;
    }

    public function whereRaw($raw, $fields = [])
    {
        $this->getWhere()->whereRaw($raw, $fields);

        return $this;
    }

    public function orWhereRaw($raw, $fields = [])
    {
        $this->getWhere()->orWhereRaw($raw, $fields);

        return $this;
    }

    public function whereNull($field)
    {
        $this->getWhere()->whereNull($field);

        return $this;
    }

    public function orWhereNull($field)
    {
        $this->getWhere()->orWhereNull($field);

        return $this;
    }

    public function whereNotNull($field)
    {
        $this->getWhere()->whereNotNull($field);

        return $this;
    }

    public function orWhereNotNull($field)
    {
        $this->getWhere()->orWhereNotNull($field);

        return $this;
    }

    public function whereIn($field, $values = [])
    {
        $this->getWhere()->whereIn($field, $values);
        return $this;
    }

    public function orWhereIn($field, $values = [])
    {
        $this->getWhere()->orWhereIn($field, $values);
        return $this;
    }

    public function whereNotIn($field, $values = [])
    {
        $this->getWhere()->whereNotIn($field, $values);
        return $this;
    }

    public function orWhereNotIn($field, $values = [])
    {
        $this->getWhere()->orWhereNotIn($field, $values);
        return $this;
    }

    public function exist($sql)
    {
        return $this->whereRaw('exists (' . $sql . ')');
    }

    public function orExist($sql)
    {
        return $this->orWhereRaw('exists (' . $sql . ')');
    }

    public function join($table, $self_key, $far_key = 'id')
    {
        if (strpos($far_key, '.') === false) {
            $far_key = $this->table . '.' . ($this->primaryKey != $far_key ? $this->primaryKey : $far_key);
        }

        $this->getJoin()->join($table, $self_key, $far_key);

        return $this;
    }

    public function leftJoin($table, $self_key, $far_key = 'id')
    {
        if (strpos($far_key, '.') === false) {
            $far_key = $this->table . '.' . ($this->primaryKey != $far_key ? $this->primaryKey : $far_key);
        }

        $this->getJoin()->join($table, $self_key, $far_key);

        return $this;
    }

    public function pass($num)
    {
        if ($num <= 0) return $this;

        $this->pass = $num;

        return $this;
    }

    /**
     * @param $num
     * @return Builder
     */
    public function limit($num)
    {
        $this->limit = $num;

        return $this;
    }

    public function orderASC($field)
    {
        $this->orders[] = $field . ' ASC';

        return $this;
    }

    public function orderDESC($field)
    {
        $this->orders[] = $field . ' DESC';

        return $this;
    }

    public function orderBy($field, $type)
    {
        $this->orders[] = $field . ' ' . $type;

        return $this;
    }

    public function orderRaw($field)
    {
        $this->orders[] = $field;

        return $this;
    }

    public function groupBy()
    {
        $args = func_get_args();
        foreach ($args as $field) {
            $this->groups[] = $field;
        }

        return $this;
    }

    public function having($field, $operator = '=', $value = '')
    {
        $this->getHaving()->having($field, $operator, $value);

        return $this;
    }

    public function orHaving($field, $operator = '=', $value = '')
    {
        $this->getHaving()->orHaving($field, $operator, $value);

        return $this;
    }

    public function havingRaw($raw, $fields = [])
    {
        $this->getHaving()->havingRaw($raw, $fields);

        return $this;
    }

    protected function getSelect()
    {
        $select = $this->table . '.*';
        if (is_array($this->fields)) {
            $select = [];
            foreach ($this->fields as $field) {
                if (!is_object($field) && strpos($field, '.') === false) {
                    $select[] = $this->table . '.' . $field;
                } else {
                    $select[] = $field;
                }
            }

            $select = implode(', ', $select);
        } else if (!is_null($this->fields)) {
            $select = $this->fields;
        }

        return $select;
    }

    protected function getBindParams()
    {
        $bind = array_merge($this->bindParams, $this->getJoin()->getBindParams());
        $bind = array_merge($bind, $this->getWhere()->getBindParams());
        $bind = array_merge($bind, $this->getHaving()->getBindParams());

        return $bind;
    }

    protected function getSelectSql($type = false)
    {
        $join = $this->getJoin()->get();
        $where = $this->getWhere()->get();
        $having = $this->getHaving()->get();

        $sql = [];

        $sql[] = 'SELECT';

        if ($type == static::TYPE_COUNT) {
            $sql[] = "\tcount(*) as count_select";
        } else {
            $sql[] = "\t" . $this->getSelect();
        }

        $sql[] = 'FROM';
        $sql[] = "\t" . $this->table;
        $sql[] = ($join ? "\t" . $join : '');
        $sql[] = ($where ? 'WHERE' . "\n\t" . $where : '');

        if (count($this->groups)) {
            $sql[] = 'GROUP BY';
            $sql[] = "\t" . implode(', ', $this->groups);
        }

        if ($having) {
            $sql[] = 'HAVING';
            $sql[] = "\t" . $having;
        }

        if ($type != static::TYPE_COUNT) {

            if (count($this->orders)) {
                $sql[] = 'ORDER BY';
                $sql[] = "\t" . implode(', ', $this->orders);
            }

            if ($this->limit) {
                $sql[] = "LIMIT " . $this->limit;
            }

            if ($this->pass) {
                $sql[] = "OFFSET " . $this->pass;
            }
        }

        return implode("\n", $sql);
    }

    public function selectSql()
    {
        $sql = $this->getSelectSql();
        $bind = $this->getBindParams();
        return SqlRaw::make($sql, $bind)->toString();
    }

    public function first()
    {
        $this->limit(1);
        $items = $this->get();

        if (count($items) == 0) return null;

        return $items[0];
    }

    public function count()
    {
        $sql = $this->getSelectSql(static::TYPE_COUNT);
        $bind = $this->getBindParams();

        $items = $this->connection->select($sql,$bind);
        return $items[0]['count_select'];
    }

    public function countSql()
    {
        $sql = $this->getSelectSql(static::TYPE_COUNT);
        $bind = $this->getBindParams();
        return SqlRaw::make($sql, $bind)->toString();
    }

    /**
     * @param int $countVisibleItems
     * @return Collection
     */
    public function paginate($countVisibleItems = 10)
    {
        $count = $this->count();

        $paginate = new Paginate($count, $countVisibleItems);

        $this->pass($paginate->getOffset())
            ->limit($paginate->getLimit())
        ;

        $sql = $this->getSelectSql();
        $bind = $this->getBindParams();

        $items = $this->connection->select($sql,$bind);

        $items = new Collection($items);

        if ($this->callback) {
            $callback = $this->callback;
            return $callback($items, $paginate);
        } else {
            $items->setPaginate($paginate);
            return $items;
        }
    }

    /**
     * @param int $countVisibleItems
     * @return Collection
     */
    public function paginateSimple($countVisibleItems = 10)
    {
        $paginate = new Paginate(false, $countVisibleItems);

        $this->pass($paginate->getOffset())
            ->limit($paginate->getLimit());

        $sql = $this->getSelectSql();
        $bind = $this->getBindParams();

        $items = $this->connection->select($sql,$bind);

        $paginate->prepareSimpleData($items);

        $items = new Collection($items);

        if ($this->callback) {
            $callback = $this->callback;
            return $callback($items, $paginate);
        } else {
            $items->setPaginate($paginate);
            return $items;
        }
    }

    /**
     * @param mixed $byKey
     * @return Collection
     */
    public function get($byKey = false)
    {
        $sql = $this->getSelectSql();
        $bind = $this->getBindParams();

        $items = $this->connection->select($sql,$bind);

        if ($byKey) {
            $__temp = [];
            foreach ($items as $row) {
                $__temp[$row[$byKey]] = $row;
            }

            $items = $__temp;
        }

        $items = new Collection($items);

        if ($this->callback) {
            $callback = $this->callback;
            return $callback($items);
        } else {
            return $items;
        }
    }

    /****** UPDATE *******/

    /**
     * @param array $updateArr
     * @return string
     */
    protected function getUpdateSql($updateArr = [])
    {
        $upd = [];
        foreach ($updateArr as $field => $value) {
            if (is_object($value) && $value instanceof Raw) {
                $upd[] = $field . ' = ' . (string)$value;
            } else {
                $upd[] = $field . ' = ?';
                $this->bindParams[] = $value;
            }
        }

        $where = $this->getWhere()->get();

        $sql = [];

        $sql[] = 'UPDATE';
        $sql[] = "\t" . $this->table;
        $sql[] = 'SET';
        $sql[] = "\t" . implode(",\n\t", $upd);
        $sql[] = ($where ? 'WHERE' . "\n\t" . $where : '');

        return implode("\n", $sql);
    }

    public function updateSql($updateArr = [])
    {
        $sql = $this->getUpdateSql($updateArr);
        $bind = $this->getBindParams();
        return SqlRaw::make($sql, $bind)->toString();
    }

    public function update($updateArr = [])
    {
        $sql = $this->getUpdateSql($updateArr);
        $bind = $this->getBindParams();

        $amount = $this->connection->update($sql,$bind);

        return $amount;
    }

    /****** INSERT *******/

    /**
     * @param array $insertArr
     * @return string
     */
    public function insertSql(array $insertArr)
    {
        $sql = $this->getInsertSql($insertArr);
        $bind = $this->getBindParams();
        return SqlRaw::make($sql, $bind)->toString();
    }

    /**
     * @param array $__inserts
     * @return string
     */
    protected function getInsertSql(array $__inserts)
    {
        if (count($__inserts) == 1) $__inserts = $__inserts[0];

        if (!isset($__inserts[0])) {
            $__inserts = [$__inserts];
        }

        $inserts = [];
        $fields = [];
        foreach ($__inserts as $__insert) {
            $insert = [];
            foreach ($__insert as $k => $v) {
                $fields[$k] = $k;

                if (is_object($v) && $v instanceof Raw) {
                    $insert[] = (string)$v;
                } else {
                    $insert[] = '?';
                    $this->bindParams[] = $v;
                }
            }
            $inserts[] = '(' . implode(', ', $insert) . ')';
        }

        $sql = [];

        $sql[] = 'INSERT INTO';
        $sql[] = "\t" . $this->table;
        if (count($fields)) {
            $sql[] = "\t" . '(' . implode(', ', $fields) . ')';
        }
        $sql[] = "VALUES";

        $sql[] = "\t" . implode(",\n\t", $inserts);

        if (count($inserts) == 1 && $this->connection->isDriver(DB::DRIVER_POSTGRESQL)) {
            $sql[] = "RETURNING *";
        }

        return implode("\n", $sql);
    }

    public function insertGetId()
    {
        $sql = $this->getInsertSql(func_get_args());
        $bind = $this->getBindParams();

        $amount = $this->connection->insert($sql,$bind);

        if ($amount == 1) {
            return $this->connection->lastInsertId();
        }

        return null;
    }

    public function insertGet()
    {
        $sql = $this->getInsertSql(func_get_args());
        $bind = $this->getBindParams();

        $stmt = $this->connection->insert($sql,$bind, true);

        if ($this->connection->isDriver(DB::DRIVER_POSTGRESQL)) {
            $item = $stmt->fetch();
        } else {
            $id = $this->connection->lastInsertId();

            $item = static::make($this->connection)->table($this->table)
                //->where('id',$id)
                ->where($this->primaryKey,$id)
                ->first();
        }

        return $item;
    }

    public function insert()
    {
        $sql = $this->getInsertSql(func_get_args());
        $bind = $this->getBindParams();

        $amount = $this->connection->insert($sql,$bind);

        return $amount;
    }

    /****** REMOVE *******/

    /**
     * @return string
     */
    protected function getDeleteSql()
    {
        $where = $this->getWhere()->get();

        $sql = [];

        $sql[] = 'DELETE FROM';
        $sql[] = "\t" . $this->table;
        $sql[] = ($where ? 'WHERE' . "\n\t" . $where : '');

        return implode("\n", $sql);
    }

    public function deleteSql()
    {
        $sql = $this->getDeleteSql();
        $bind = $this->getBindParams();
        return SqlRaw::make($sql, $bind)->toString();
    }

    public function delete()
    {
        $sql = $this->getDeleteSql();
        $bind = $this->getBindParams();

        $amount = $this->connection->delete($sql,$bind);

        return $amount;
    }
}