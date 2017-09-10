<?php

namespace Spirit\Structure;

use Spirit\DB;
use Spirit\Collection;
use Spirit\Collection\Paginate;
use Spirit\DB\Builder;
use Spirit\DB\Connection;
use Spirit\Structure\Model\HelperTrait;
use Spirit\Structure\Model\MutatorTrait;
use Spirit\Structure\Model\RelationTrait;
use Spirit\Structure\Model\Relations\Relation;
use Spirit\Structure\Model\ScopeTrait;
use Spirit\Structure\Model\SoftRemoveTrait;
use Spirit\Structure\Arrayable;
use ArrayAccess;
use JsonSerializable;
use Spirit\Structure\Jsonable;

/**
 * Class Model
 * @package Spirit
 * @author Marat Nuriev
 *
 * @property integer $id
 *
 * @method static Builder|Model where(...$param)
 * @method static Builder|Model orWhere(...$param)
 * @method static Builder|Model whereIn(...$param)
 * @method static Builder|Model orderBy(...$param)
 * @method static Builder|Model join(...$param)
 * @method static Builder|Model with(...$param)
 * @method static Builder|Model whereHas(...$param)
 * @method Collection|Model[] get()
 * @method string getSql()
 * @method integer delete()
 * @method integer count()
 * @method string countSql()
 * @method Collection paginate(...$param)
 * @method static first()
 *
 * @mixin SoftRemoveTrait
 */
abstract class Model implements ArrayAccess, Arrayable, JsonSerializable, Jsonable
{

    use RelationTrait, MutatorTrait, ScopeTrait, HelperTrait;

    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';

    protected static $ownMethods;

    protected static $returnBuilderMethods = [
        'get',
        'first',
        'paginate',
        'update',
        'insert',
        'delete',
        'selectSql',
        'countSql',
        'updateSql',
        'insertSql',
        'deleteSql',
    ];

    protected static $callInnerMethods = [
        'with' => 'withRelation',
        'whereHas' => 'whereHasRelation'
    ];
    /**
     * @var null|Connection
     */
    protected $connection = null;
    protected $id;
    protected $primaryKey = 'id';
    protected $table = 'table';
    protected $data = [];
    protected $saveData = [];
    /**
     * Какие поля можно заполнять
     *
     * @var array
     */
    protected $fillable = [
        '*'
    ];
    /**
     * Какие поля защищены от записи
     *
     * @var array
     */
    protected $protect = [

    ];
    protected $hidden = [

    ];
    protected $visible = [

    ];
    /**
     * Правила для валидации
     *
     * @var array
     */
    protected $rules = [

    ];
    /**
     * Названия для аттрибутов
     *
     * @var array
     */
    protected $title = [

    ];
    /**
     * @var Builder
     */
    protected $queryBuilder;
    protected $timestamps = false;

    public function __construct($data = [], $fill = true)
    {
        $this->data = $data;

        if ($data && isset($data[$this->primaryKey])) {
            $this->id = $data[$this->primaryKey];
        }

        if ($fill) {
            $this->fill($data);
        }

        if (property_exists($this, 'isSoftRemove') && $this->isSoftRemove) {
            $this->initSoftRemove();
        }
    }

    public function fill($data)
    {
        foreach($data as $name => $value) {

            if (in_array($name, $this->protect, true))
                continue;

            $this->saveData[$name] = $value;
            $this->data[$name] = $value;
        }
    }

    /**
     * @param $method
     * @param array $args
     * @return static|Model|$this
     */
    public static function __callStatic($method, array $args = [])
    {
        return static::make()->{$method}($args);
    }

    /**
     * @param bool|array $data
     * @param bool $fill
     * @return $this
     */
    public static function make($data = [], $fill = true)
    {
        return new static($data, $fill);
    }

    /**
     * @param $id
     *
     * @return $this|static|Collection|static[]
     */
    public static function find($id)
    {
        $class = static::make();

        if (is_array($id)) {
            $result = $class->whereIn($class->getPrimaryKey(), $id)
                ->get();
        } else {
            $result = $class->where($class->getPrimaryKey(), $id)
                ->first();

            if (!$result)
                return null;
        }

        return $result;
    }

    /**
     * Достать все записи
     *
     * @return $this[]|static[]|Model[]|Collection
     */
    public static function all()
    {
        return static::make()
            ->get();
    }

    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    public function offsetUnset($offset)
    {
        unset($this->$offset);
    }

    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    public function __debugInfo()
    {
        $data = $this->data;

        foreach($this->relationLoaded as $k => $v) {
            $data[$k] = $v;
        }

        return $data;
    }

    public function __get($field)
    {
        if (in_array($field, $this->hidden, true)) {
            return null;
        }

        if (method_exists($this, $field)) {
            if (array_key_exists($field, $this->relationLoaded) && $this->relationLoaded[$field]) {
                return $this->relationLoaded[$field];
            }

            if (in_array($field, static::getOwnMethods())) {
                return null;
            }

            /**
             * @var Relation $hasClass
             */
            $hasClass = $this->{$field}();

            return $this->relationLoaded[$field] = $hasClass->get();
        }

        $value = null;
        if (array_key_exists($field, $this->data)) {
            $value = $this->data[$field];
        }

        return $this->mutatorGet($field, $value);
    }

    public function __set($name, $value)
    {
        $value = $this->mutatorSet($name, $value);

        $this->saveData[$name] = $value;
        $this->data[$name] = $value;
    }

    public static function getOwnMethods()
    {
        if (static::$ownMethods) {
            return static::$ownMethods;
        }

        return static::$ownMethods = get_class_methods(Model::class);
    }

    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    public function __unset($name)
    {
        unset($this->data[$name]);
    }

    /**
     * @param $method
     * @param array $args
     * @return $this|mixed
     */
    public function __call($method, array $args)
    {
        if (isset(static::$callInnerMethods[$method])) {
            // Внутриние защищённые методы
            return $this->{static::$callInnerMethods[$method]}(...$args);
        }

        if (in_array($method, static::$returnBuilderMethods) && !in_array($method, ['insert', 'insertSql'])) {
            $this->scopeAdd();
        } elseif ($r = $this->scopeCall($method, $args)) {
            return $r;
        }

        $qb = $this->getQueryBuilder();

        $result = $qb->{$method}(...$args);

        if (in_array($method, static::$returnBuilderMethods)) {
            return $result;
        }

        return $this;
    }

    /**
     * @return Builder
     */
    public function getQueryBuilder()
    {
        if ($this->queryBuilder) {
            return $this->queryBuilder;
        }

        $this->queryBuilder = DB::connect($this->connection)
            ->table($this->table, [$this, 'queryBuilderCallback']);

        if ($this->id) {
            $this->queryBuilder->where($this->primaryKey, $this->id);
        }

        return $this->queryBuilder;
    }

    /**
     * @param Collection $items
     * @param Paginate $paginate
     * @return array|Collection
     */
    public function queryBuilderCallback(Collection $items, Paginate $paginate = null)
    {
        $primaryKeyField = $this->primaryKey;

        /**
         * @var Model $newItems []
         */
        $newItems = [];
        $keysToItemID = [];
        foreach($items as $k => $v) {
            /**
             * @var Model $o
             */
            $o = static::make($v, false);

            foreach($this->relations as $relationName => $relationHasClass) {
                $o->setHasLoad($relationName, $relationHasClass->emptyValue());
            }

            $newItems[$k] = $o;

            $keysToItemID[$k] = $o->$primaryKeyField;
        }

        if (count($keysToItemID)) {
            $itemIDToKey = array_flip($keysToItemID);

            foreach($this->relations as $relationName => $relationHasClass) {
                $result = $relationHasClass->getWhereIn($keysToItemID);

                foreach($result as $id => $resultItems) {
                    /**
                     * @var Model $modelItem
                     */
                    $modelItem = $newItems[$itemIDToKey[$id]];
                    $modelItem->setHasLoad($relationName, $resultItems);
                }
            }
        }

        $c = Collection::make($newItems);

        if ($paginate) {
            $c->setPaginate($paginate);
        }

        return $c;
    }

    public function save()
    {
        if (count($this->saveData) == 0) {
            return false;
        }

        $fullSave = isset($this->fillable[0]) && $this->fillable[0] == '*';
        $saveOptions = [];

        if (!$fullSave) {
            foreach($this->fillable as $field) {
                $saveOptions[$field] = true;
            }
        }

        foreach($this->protect as $field) {
            $saveOptions[$field] = false;
        }

        $set = [];
        foreach($this->saveData as $field => $value) {
            if ($fullSave || (isset($saveOptions[$field]) && $saveOptions[$field])) {
                $set[$field] = $value;
            }
        }

        if (count($set) == 0) {
            return false;
        }

        if ($this->id) {
            $sql = $this->getQueryBuilder();

            if ($this->timestamps) {
                $set[static::UPDATED_AT] = DB::raw('NOW()');
            }

            $result = $sql->update($set);
            $this->queryBuilder = null;

            if ($result == 0) {
                return false;
            }
        } else {
            if ($this->timestamps) {
                $set[static::CREATED_AT] = DB::raw('NOW()');
            }

            $result = $this->getQueryBuilder()
                ->withPrimaryKey($this->primaryKey)
                ->insertGet($set);

            $this->queryBuilder = null;

            if (!$result) {
                return false;
            }

            $this->data = $result;

            $this->id = $result[$this->primaryKey];

        }

        return true;
    }

    public function remove()
    {
        if (!$this->id)
            return false;

        if (property_exists($this, 'isSoftRemove') && $this->isSoftRemove) {
            $this->softRemove();
        } else {
            $this->getQueryBuilder()
                ->delete();
            $this->queryBuilder = null;
        }

        //$this->id = null;
        $this->data = [];

        return true;
    }

    public function touch()
    {
        if (!$this->timestamps) {
            return;
        }

        $this->getQueryBuilder()
            ->update([
                static::UPDATED_AT => DB::raw('NOW()')
            ]);

        $this->queryBuilder = null;
    }

    /**
     * @param  int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        $json = json_encode($this->jsonSerialize(), $options);

        //        if (JSON_ERROR_NONE !== json_last_error()) {
        //            json_last_error_msg()
        //        }

        return $json;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function toArray()
    {
        $d = [];
        foreach($this->data as $key => $value) {
            if (in_array($key, $this->hidden, true))
                continue;

            $d[$key] = $this->{$key};
        }

        foreach($this->visible as $key) {
            $d[$key] = $this->{$key};
        }

        foreach($d as $key => $value) {
            if ($value instanceof Arrayable) {
                $d[$key] = $value->toArray();
            }
        }

        return $d;
    }
}