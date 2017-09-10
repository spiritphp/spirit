<?php

namespace Spirit\Services;

use Spirit\Collection;
use Spirit\Structure\Service;
use Spirit\Structure\Model;

class Table extends Service
{

    const N_TITLE = 'title';
    const N_VALUE = 'value';
    const N_LINK = 'link';
    const N_WIDTH = 'width';
    const N_STYLE = 'style';
    const N_CLASS = 'class';
    const N_LINK_CLASS = 'link_class';

    public static function make($data)
    {
        return new Table($data);
    }

    protected $lastKey;

    /**
     * @var Collection|array
     */
    protected $data;

    protected $columns = [];

    public function __construct($data)
    {
        parent::__construct();
        $this->data = $data;
    }

    public function __toString()
    {
        return $this->draw();
    }

    /**
     * @param $key
     * @param $title
     * @param mixed $value
     * @return $this|Table
     */
    public function addColumn($key, $title, $value = false)
    {
        $this->lastKey = $key;

        $this->columns[$key] = [
            static::N_TITLE => ($title ? $title : $key),
            static::N_VALUE => $value
        ];

        return $this;
    }

    protected function setOptions($t, $key, $value)
    {
        if ($value == false) {
            $value = $key;
            $key = $this->lastKey;
        }

        if (isset($this->columns[$key])) {
            $this->columns[$key][$t] = $value;
        }

        return $this;
    }

    public function setLink($key, $value = false)
    {
        return $this->setOptions(static::N_LINK, $key, $value);
    }

    public function setWidth($key, $value = false)
    {
        return $this->setOptions(static::N_WIDTH, $key, $value);
    }

    public function setStyle($key, $value = false)
    {
        return $this->setOptions(static::N_STYLE, $key, $value);
    }

    public function setClass($key, $value = false)
    {
        return $this->setOptions(static::N_CLASS, $key, $value);
    }

    protected function getPrepareList()
    {
        $data = [];
        foreach ($this->data as $raw) {

            $d = [];
            foreach ($this->columns as $k => $option) {

                $columnData = [];

                if ($raw instanceof Model) {
                    $rawValue = $raw->$k;
                } else {
                    $rawValue = isset($raw[$k]) ? $raw[$k] : null;
                }


                if (!isset($option[static::N_VALUE])) {
                    continue;
                }

                if (is_string($option[static::N_VALUE])) {
                    $rawValue = $option[static::N_VALUE];
                } elseif (is_callable($option[static::N_VALUE])) {
                    $rawValue = $option[static::N_VALUE]($raw);
                }

                $columnData[static::N_VALUE] = $rawValue;

                if (isset($option[static::N_LINK])) {

                    if (is_string($option[static::N_LINK])) {
                        $link = $option[static::N_LINK];
                        $linkOption = false;
                    } else {
                        $link = $option[static::N_LINK][0];
                        $linkOption = $option[static::N_LINK][1];
                    }

                    $link =
                        preg_replace_callback(
                            "/{([a-z_]+)}/iu",
                            function ($matches) use ($raw) {
                                $k = strtolower($matches[1]);
                                if ($raw instanceof Model) {
                                    return $raw->$k;
                                } else {
                                    return isset($raw[$k]) ? $raw[$k] : '';
                                }

                            },
                            $link
                        );

                    $columnData[static::N_LINK] = [$link, $linkOption];
                }

                if (isset($option[static::N_WIDTH])) {
                    $columnData[static::N_WIDTH] = $option[static::N_WIDTH];
                }

                if (isset($option[static::N_STYLE])) {
                    $columnData[static::N_STYLE] = $option[static::N_STYLE];
                }

                if (isset($option[static::N_CLASS])) {
                    $columnData[static::N_CLASS] = $option[static::N_CLASS];
                }

                $d[$k] = $columnData;
            }

            $data[] = $d;
        }

        return $data;
    }

    protected function getPrepareColumn()
    {
        $column = [];
        foreach ($this->columns as $k => $option) {
            $column[$k] = $option[static::N_TITLE];
        }

        return $column;
    }

    public function draw($view = null)
    {
        $data = [
            'items' => $this->getPrepareList(),
            'columns' => $this->getPrepareColumn()
        ];

        if ($this->data instanceof Collection) {
            if ($this->data->checkPaginate()) {
                $data['page'] = $this->data->paginate()->draw();
            }
        }

        if (is_null($view)) {
            $view = '{__SPIRIT__}/services/table/default.php';
        }

        return $this->view($view, $data)->render();
    }
}