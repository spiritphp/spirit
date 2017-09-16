<?php

namespace Spirit\View;

use Spirit\Func\Str;
use Spirit\View;

/**
 * Class Layout
 * @package Spirit\View
 *
 * @method in($block_name)
 * @method append($block_name)
 * @method prepend($block_name)
 */
class Layout
{
    const ACTION_IN = 'in';
    const ACTION_APPEND = 'append';
    const ACTION_PREPEND = 'prepend';

    public static $actions = [
        self::ACTION_IN,
        self::ACTION_PREPEND,
        self::ACTION_APPEND
    ];

    protected $path;
    protected $extendedByView = null;
    protected $defaultBlock = null;
    protected $blocks = [];
    protected $contents = [];

    public static function make($path)
    {
        return new static($path);
    }

    public function __construct($path)
    {
        $this->path = $path;
    }

    public function extendedBy($view)
    {
        $this->extendedByView = $view;

        return $this;
    }

    public function __call($name, $arguments)
    {
        if (!in_array($name, static::$actions, true)) {
            throw new \Exception('Action for layout is not found');
        }

        if (!$this->defaultBlock && !isset($arguments[0])) {
            throw new \Exception('The name of block is empty');
        }

        $this->blocks[] = [
            'block' => isset($arguments[0]) ? $arguments[0] : $this->defaultBlock,
            'action' => $name
        ];

        $this->contentStart();
    }

    public function save()
    {
        $content = $this->contentFinish();

        $current = array_pop($this->blocks);
        $currentAction = $current['action'];
        $currentBlock = $current['block'];


        if ($currentAction === static::ACTION_IN) {
            $this->contents[$currentBlock] = $content;
        } elseif ($currentAction === static::ACTION_APPEND || $currentAction === static::ACTION_PREPEND) {
            if (!isset($this->contents[$currentBlock])) {
                $this->contents[$currentBlock] = [];
            } elseif (!is_array($this->contents[$currentBlock])) {
                $this->contents[$currentBlock] = [$this->contents[$currentBlock]];
            }

            if ($currentAction === static::ACTION_PREPEND) {
                array_unshift($this->contents[$currentBlock], $content);
            } else {
                $this->contents[$currentBlock][] = $content;
            }

        } else {
            $method = 'save' . ucfirst(Str::toCamelCase($currentAction));

            if (!method_exists($this, $method)) {
                throw new \Exception('Method «' . $method . '» is not exist');
            }

            $this->{$method}();
        }
    }

    public function view($path, $data = [])
    {
        echo View::make($path, $data)->render();
    }

    public function setDefaultBlock($block)
    {
        $this->defaultBlock = $block;
        return $this;
    }

    public function setBlockContent($block, $content = null)
    {
        if ($block && is_null($content)) {
            $content = $block;
            $block = $this->defaultBlock;
        }

        if (is_null($block)) {
            throw new \Exception('The name of block is null');
        }

        $this->contents[$block] = $content;
        return $this;
    }

    public function block($block)
    {
        if (!isset($this->contents[$block])) {
            return null;
        }

        $content = $this->contents[$block];

        return is_array($content) ? implode("\n", $content) : $content;
    }

    public function render()
    {
        return View::make($this->path)->render();
    }

    public function isExtendingView($path)
    {
        return $path === $this->extendedByView;
    }

    public function __toString()
    {
        return $this->render();
    }

    protected function contentStart()
    {
        ob_start();
    }

    protected function contentFinish()
    {
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }
}