<?php

namespace Spirit;

use Spirit\Collection\Paginate;
use Spirit\Func\Arr;
use Spirit\Func\Data;
use Spirit\Structure\Arrayable;
use Spirit\Structure\Jsonable;
use Countable;
use ArrayAccess;
use JsonSerializable;
use IteratorAggregate;

class Collection implements ArrayAccess, Arrayable, Countable, IteratorAggregate, JsonSerializable, Jsonable
{
    /**
     * @var Paginate
     */
    protected $paginate;

    protected $count = 0;
    protected $items = [];

    public function __construct($items = [])
    {
        $this->items = $items;
        $this->count = count($items);
    }

    public function __debugInfo()
    {
        return $this->items;
    }

    public function toArray()
    {
        return array_map(function ($value) {
            if ($value instanceof Arrayable) {
                return $value->toArray();
            }

            return $value;
        }, $this->items);
    }

    public function all()
    {
        return $this->items;
    }

    public function count()
    {
        return $this->count;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->items[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->items[$offset]) ? $this->items[$offset] : null;
    }

    /**
     * @param  int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        if ($options === 0) $options = JSON_UNESCAPED_UNICODE;
        return json_encode($this->jsonSerialize(), $options);
    }

    public function jsonSerialize()
    {
        return array_map(function ($value) {
            if ($value instanceof JsonSerializable) {
                return $value->jsonSerialize();
            } elseif ($value instanceof Jsonable) {
                return json_decode($value->toJson(), true);
            } elseif ($value instanceof Arrayable) {
                return $value->toArray();
            } else {
                return $value;
            }
        }, $this->items);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * Достать записи по ключу
     *
     * @param $key
     * @return Collection
     */
    public function byKey($key)
    {
        $newArr = [];
        foreach ($this->items as $k => $v) {


            $keyVal = isset($v[$key]) ? $v[$key] : false;

            if ($keyVal) {
                $newArr[$keyVal] = $v;
            } else {
                $newArr[$k] = $v;
            }
        }

        return static::make($newArr);
    }

    public static function make($items)
    {
        return new static($items);
    }

    public function keys()
    {
        $newArr = [];
        foreach ($this->items as $k => $v) {
            $newArr[$k] = $k;
        }

        return $newArr;
    }

    public function pluck($key1, $key2 = false)
    {
        $newArr = [];
        foreach ($this->items as $k => $v) {

            $keyValue2 = isset($v[$key2]) ? $v[$key2] : false;
            $keyValue1 = $v[$key1];

            if (!$keyValue2) {
                $newArr[] = $keyValue1;
            } else {
                $newArr[$keyValue2] = $keyValue1;
            }
        }

        return $newArr;
    }

    /**
     * Установить постраничник
     *
     * @param Paginate $paginate
     */
    public function setPaginate(Paginate $paginate)
    {
        $this->paginate = $paginate;
    }

    public function checkPaginate()
    {
        return $this->paginate ? true : false;
    }

    /**
     * @param int $countVisibleItems
     *
     * @return Paginate
     */
    public function paginate($countVisibleItems = 10)
    {
        if (is_null($this->paginate)) {
            $this->paginate = new Paginate($this->count, $countVisibleItems);
        }

        return $this->paginate;
    }

    public function chunk($size)
    {
        if ($size <= 0) {
            return new static([]);
        }

        $chunks = [];

        foreach (array_chunk($this->items, $size, true) as $chunk) {
            $chunks[] = new static($chunk);
        }

        return new static($chunks);
    }

    public function collapse()
    {
        return new static(Arr::collapse($this->items));
    }

    public function groupBy($groupBy, $saveKeys = false)
    {
        /**
         * @var Collection[] $groups
         */
        $groups = [];
        foreach ($this->items as $key => $item) {

            if (is_callable($groupBy)) {
                $groupKeys = $groupBy($item);
            } else {
                $groupKeys = isset($item[$groupBy]) ? $item[$groupBy] : null;
            }

            if (is_null($groupKeys)) {
                continue;
            }

            if (!is_array($groupKeys)) {
                $groupKeys = [$groupKeys];
            }

            foreach ($groupKeys as $groupKey) {
                $groupKey = is_bool($groupKey) ? (int)$groupKey : $groupKey;
                if (!array_key_exists($groupKey, $groups)) {
                    $groups[$groupKey] = new static;
                }
                $groups[$groupKey]->offsetSet($saveKeys ? $key : null, $item);
            }

        }

        return new static($groups);
    }

    public function where($key, $operator, $value = null)
    {
        if (is_null($value)) {
            $value = $operator;

            $operator = '=';
        }

        return $this->filter($this->operatorForWhere($key, $operator, $value));
    }

    public function filter(callable $callback = null)
    {
        if ($callback) {
            $items = array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH);
        } else {
            $items = array_filter($this->items);
        }

        return new static($items);
    }

    /**
     *
     * @param  string $key
     * @param  string $operator
     * @param  mixed $value
     * @return \Closure
     */
    protected function operatorForWhere($key, $operator, $value)
    {
        return function ($item) use ($key, $operator, $value) {
            $retrieved = Data::get($item, $key);

            switch ($operator) {
                default:
                case '=':
                case '==':
                    return $retrieved == $value;
                case '!=':
                case '<>':
                    return $retrieved != $value;
                case '<':
                    return $retrieved < $value;
                case '>':
                    return $retrieved > $value;
                case '<=':
                    return $retrieved <= $value;
                case '>=':
                    return $retrieved >= $value;
                case '===':
                    return $retrieved === $value;
                case '!==':
                    return $retrieved !== $value;
            }
        };
    }

    /**
     * @param string $key
     * @param mixed $values
     * @param bool $strict
     * @return Collection
     */
    public function whereIn($key, $values, $strict = false)
    {
        return $this->filter(function ($item) use ($key, $values, $strict) {
            return in_array(Data::get($item, $key), $values, $strict);
        });
    }

    /**
     * @param  string $key
     * @param  mixed $values
     * @param  bool $strict
     * @return static
     */
    public function whereNotIn($key, $values, $strict = false)
    {
        return $this->reject(function ($item) use ($key, $values, $strict) {
            return in_array(Data::get($item, $key), $values, $strict);
        });
    }

    public function reject(callable $callback = null)
    {
        if ($callback) {
            $items = array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH);
        } else {
            $items = array_filter($this->items);
        }

        $items = array_diff_key($this->items, $items);

        return new static($items);
    }

    public function sort(callable $callback = null)
    {
        $items = $this->items;

        $callback ? uasort($items, $callback) : asort($items);

        return new static($items);
    }

    public function sortMultiple($fields = [], $type = 'ASC', $options = SORT_NATURAL)
    {
        return $this->sort(function ($item1, $item2) use ($fields, $type, $options) {

            $c = 0;
            foreach ($fields as $field) {
                $v1 = is_callable($field) ? $field($item1) : $item1[$field];
                $v2 = is_callable($field) ? $field($item2) : $item2[$field];

                if ($type = SORT_NATURAL) {
                    $c = strnatcmp($v1, $v2);
                } else {
                    $c = strcmp($v1, $v2);
                }

                if ($c !== 0) {
                    break;
                }
            }

            return strtoupper($type) === 'DESC' ? $c * -1 : $c;
        });
    }

    public function sortByDesc($callback, $options = SORT_REGULAR)
    {
        return $this->sortBy($callback, 'DESC', $options);
    }

    /**
     * @param $callback
     * @param string $type
     * @param int $options
     * @return static
     */
    public function sortBy($callback, $type = 'ASC', $options = SORT_REGULAR)
    {
        $results = [];
        foreach ($this->items as $key => $value) {
            if (is_callable($callback)) {
                $results[$key] = call_user_func_array($callback, [$value, $key]);
            } else {
                $results[$key] = $value[$callback];
            }
        }

        strtoupper($type) === 'DESC' ? arsort($results, $options) : asort($results, $options);

        foreach (array_keys($results) as $key) {
            $results[$key] = $this->items[$key];
        }

        return new static($results);
    }

    public function values()
    {
        return new static(array_values($this->items));
    }
}