<?php

namespace Spirit\Console;

use Spirit\Func\Str;

class Table
{

    public static function make()
    {
        return new static();
    }

    public static function strlen($v)
    {
        return mb_strlen(preg_replace("/\033\[[^\s]+m/ius", '', str_replace("\033[0m", '', $v)), "UTF-8");
    }


    protected $header = [];
    protected $rows = [];

    protected $columnsWidth = [];
    protected $tableWidth = 1;
    protected $padding = 1;
    protected $border = null;

    public function __construct()
    {
    }

    public function setHeader($v)
    {
        $this->header = $v;
        return $this;
    }

    public function setRows($v)
    {
        $this->rows = $v;
        return $this;
    }

    public function addRow($v)
    {
        $this->rows[] = $v;
        return $this;
    }

    public function setAssoc($arr)
    {
        foreach ($arr as $k => $v) {
            $this->rows[] = [
                $k,
                $v
            ];
        }

        return $this;
    }

    public function render()
    {
        $this->initColumnsLength();

        $table[] = $this->border;
        if (count($this->header)) {
            $table[] = $this->getRow($this->header);
            $table[] = $this->border;
        }

        foreach ($this->rows as $row) {
            $table[] = $this->getRow($row);
            $table[] = $this->border;
        }

        return implode("\n", $table) . "\n";
    }

    protected function getRow($data)
    {
        $amountTr = 1;
        $rowArr = [];
        foreach ($data as $item) {

            $item = Str::toString($item);

            $arr = explode("\n", $item ? $item : '');
            $amount = count($arr);
            if ($amount > $amountTr) {
                $amountTr = $amount;
            }

            $rowArr[] = $arr;
        }

        $rows = [];
        for ($tr = 0; $tr < $amountTr; ++$tr) {
            $row = [];
            $row[] = '|';
            foreach ($this->columnsWidth as $i => $length) {
                if (isset($rowArr[$i][$tr])) {
                    $_length = static::strlen($rowArr[$i][$tr]);
                    $row[] = str_repeat(' ', $this->padding) . $rowArr[$i][$tr] . str_repeat(' ', ($this->padding + $length - $_length));
                } else {
                    $row[] = str_repeat(' ', ($length + $this->padding * 2));
                }
                $row[] = '|';
            }
            $rows[] = implode('', $row);
        }

        return implode("\n", $rows);
    }

    protected function initColumnsLength()
    {
        $i = 0;
        foreach ($this->header as $item) {

            if (is_array($item)) {
                $item = json_encode($item, JSON_UNESCAPED_UNICODE);
            }

            $arr = explode("\n", $item ? $item : '');
            foreach ($arr as $v) {
                $length = static::strlen($v);
                if (!isset($this->columnsWidth[$i])) {
                    $this->columnsWidth[$i] = $length;
                } elseif ($this->columnsWidth[$i] < $length) {
                    $this->columnsWidth[$i] = $length;
                }
            }
            ++$i;
        }

        foreach ($this->rows as $row) {
            $i = 0;
            foreach ($row as $item) {

                $arr = explode("\n", Str::toString($item));

                foreach ($arr as $v) {
                    $length = static::strlen($v);
                    if (!isset($this->columnsWidth[$i])) {
                        $this->columnsWidth[$i] = $length;
                    } elseif ($this->columnsWidth[$i] < $length) {
                        $this->columnsWidth[$i] = $length;
                    }
                }

                ++$i;
            }
        }

        $this->border = '+';
        foreach ($this->columnsWidth as $length) {
            $this->tableWidth += $length + $this->padding * 2 + 1;
            $this->border .= str_repeat('-', ($length + $this->padding * 2)) . '+';
        }
    }
}