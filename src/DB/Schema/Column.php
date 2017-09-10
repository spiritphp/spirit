<?php

namespace Spirit\DB\Schema;

use Spirit\DB\Raw;
use Spirit\DB;

class Column
{
    const TYPE_STRING = 'string';
    const TYPE_TEXT = 'text';
    const TYPE_INTEGER = 'integer';
    const TYPE_BIGINTEGER = 'bigInteger';
    const TYPE_TIMESTAMP = 'timestamp';
    const TYPE_DATE = 'date';
    const TYPE_JSON = 'json';
    const TYPE_JSONB = 'jsonb';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_SMALLINTEGER = 'smallInteger';
    const TYPE_NUMERIC = 'numeric';
    const TYPE_DECIMAL = 'decimal';
    const TYPE_INET = 'inet';
    const TYPE_MONEY = 'money';


    public static $options = [
        self::TYPE_INTEGER => [
            'default' => 'integer',
        ],
        self::TYPE_BIGINTEGER => [
            'default' => 'bigint',
        ],
        self::TYPE_SMALLINTEGER => [
            'default' => 'smallint',
        ],
        self::TYPE_TEXT => [
            'default' => 'text',
        ],
        self::TYPE_TIMESTAMP => [
            'default' => 'timestamp without time zone',
            'mysql' => 'timestamp',
        ],
        self::TYPE_DATE => [
            'default' => 'date',
        ],
        self::TYPE_JSON => [
            'default' => 'json',
        ],
        self::TYPE_JSONB => [
            'default' => 'jsonb',
            'mysql' => 'json'
        ],
        self::TYPE_BOOLEAN => [
            'default' => 'boolean',
        ],
        self::TYPE_INET => [
            'default' => 'inet',
            'mysql' => 'character varying(40)',
        ],
        self::TYPE_STRING => [
            'default' => 'character varying(:length)',
            'settings' => [
                'length' => 255
            ]
        ],
        self::TYPE_NUMERIC => [
            'default' => 'numeric(:length,:accuracy)',
            'mysql' => 'decimal(:length,:accuracy)',
            'settings' => [
                'length' => 19,
                'accuracy' => 4,
            ]
        ],
        self::TYPE_DECIMAL => [
            'default' => 'decimal(:length,:accuracy)',
            'pgsql' => 'numeric(:length,:accuracy)',
            'settings' => [
                'length' => 19,
                'accuracy' => 4,
            ]
        ],
        self::TYPE_MONEY => [
            'default' => 'numeric(:length,:accuracy)',
            'mysql' => 'decimal(:length,:accuracy)',
            'settings' => [
                'length' => 19,
                'accuracy' => 4,
            ]
        ],
    ];

    /**
     * @param Table $table
     * @param $name
     * @param null $type
     * @return Column
     */
    public static function make(Table $table, $name, $type = null)
    {
        return new Column($table, $name, $type);
    }

    /**
     * @var Table
     */
    protected $table;
    protected $name;
    protected $newName;
    protected $type;
    protected $length;
    protected $accuracy;
    protected $default;
    protected $notNull = false;
    protected $autoIncrement = false;
    protected $checkExists = false;

    /**
     * Column constructor.
     * @param Table $table
     * @param $name
     * @param $type
     */
    public function __construct(Table $table, $name, $type)
    {
        $this->name = $name;
        $this->type = $type;
        $this->table = $table;
    }

    public function length($val)
    {
        $this->length = $val;

        return $this;
    }

    public function accuracy($val)
    {
        $this->accuracy = $val;

        return $this;
    }

    public function setDefault($val)
    {
        if (is_object($val) && $val instanceof Raw) {

        } else {
            if ($val === false) {
                $val = DB::raw('false');
            } elseif ($val === true) {
                $val = DB::raw('true');
            } elseif (strtolower($val) === 'now()') {
                $val = DB::raw('CURRENT_TIMESTAMP');
            }
        }

        $this->default = $val;

        return $this;
    }

    public function setNewName($val)
    {
        $this->newName = $val;

        return $this;
    }

    public function setCheckExists($v = true)
    {
        $this->checkExists = $v;

        return $this;
    }

    public function autoIncrement()
    {
        $this->autoIncrement = true;

        return $this;
    }

    public function notNull()
    {
        $this->notNull = true;

        return $this;
    }

    public function needCheckExists()
    {
        return $this->checkExists;
    }

    protected function getColumnSql()
    {
        $option = static::$options[$this->type];

        $driver_name = $this->table->connection()->getDriverName();

        if (isset($option[$driver_name])) {
            $type = $option[$driver_name];
        } else {
            $type = $option['default'];
        }

        if (isset($option['settings']['length'])) {
            $type = str_replace(
                ':length',
                ($this->length ? $this->length : $option['settings']['length']),
                $type
            );
        }

        if (isset($option['settings']['accuracy'])) {
            $type = str_replace(
                ':accuracy',
                ($this->accuracy ? $this->accuracy : $option['settings']['accuracy']),
                $type
            );
        }

        $sql_column = [
            $this->name,
            $type
        ];

        if (!is_null($this->default)) {
            if (is_object($this->default) && $this->default instanceof Raw) {
                $default = (string)$this->default;
            } else {
                $default = "'" . $this->default . "'";
            }

            $sql_column[] = 'DEFAULT ' . $default;
        } elseif($this->table->isMySQL() && !$this->notNull) {
            if ($type === static::TYPE_TIMESTAMP) {
                $sql_column[] = 'NULL';
            } else {
                $sql_column[] = 'DEFAULT NULL';
            }
        }

        if ($this->notNull) {
            $sql_column[] = 'NOT NULL';
        }

        if ($this->autoIncrement) {
            $sql_column[] = 'PRIMARY KEY AUTO_INCREMENT';
        }

        return implode(' ', $sql_column);
    }

    public function getSqlForCreateTable()
    {
        return $this->getColumnSql();
    }

    public function getSqlForCreate()
    {
        return
            'ALTER TABLE ' . $this->table->getName() .
            ' ADD COLUMN ' . $this->getColumnSql();
    }

    public function getSqlForDrop()
    {
        return
            'ALTER TABLE ' . $this->table->getName() .
            ' DROP COLUMN ' . ($this->checkExists ? 'IF EXISTS ' : '') . $this->name;
    }

    public function getSqlForRename()
    {
        return
            'ALTER TABLE ' . $this->table->getName() .
            ' RENAME COLUMN ' . $this->name . ' TO ' . $this->newName;
    }

}