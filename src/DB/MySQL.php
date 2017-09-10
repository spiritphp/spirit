<?php

namespace Spirit\DB;

use Spirit\DB;

class MySQL extends Connection
{
    protected $driver = DB::DRIVER_MYSQL;

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
                information_schema.TABLES
            WHERE
                table_schema = ? AND
                table_name = ?
            LIMIT 1
        ", [$this->database, $table_name]);

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
                information_schema.COLUMNS
            WHERE
                table_schema = ? AND
                table_name = ? AND
                column_name = ?
            LIMIT 1
        ", [$this->database, $table_name, $column_name]);

        return !!$stmt->fetch();
    }
}