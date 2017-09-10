<?php

namespace Spirit;

use Spirit\Constructor\Components\Debug;
use Spirit\Structure\Component;
use Spirit\View\Layout;
use Spirit\View\Template;

class Constructor
{

    const CONTENT = 'content';
    const DEBUG = 'debug';

    protected $blocks = [];

    protected $isAdmin = false;

    protected $isOnlyControllerResponse = false;
    protected $isJSON = false;
    protected $isJSONP = false;
    protected $isXML = false;
    protected $headers = [];

    /**
     * @var string|Response
     */
    protected $content = null;

    public static function make()
    {
        return new Constructor();
    }

    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @param $view
     * @param array $data
     * @param bool $key
     * @return $this
     */
    public function addComponent($view, $data = [], $key = false)
    {
        if (mb_substr($view, 0, 1, "UTF-8") !== '/') {
            $view = Engine::dir()->views_component . $view;
        }

        return $this->addView($view, $data, $key);
    }

    /**
     * @param $view
     * @param array $data
     * @param bool $key
     * @return $this
     */
    public function addView($view, $data = [], $key = false)
    {
        return $this->add($key, function () use ($view, $data) {
            return View::make($view, $data);
        });
    }

    /**
     * @param $key
     * @param string|Component|callable $block
     * @return $this
     */
    public function add($key, $block = null)
    {
        if (is_null($block)) {
            $block = $key;
            $key = false;
        }

        if ($key) {
            $this->blocks[$key] = $block;
        } else {
            $this->blocks[] = $block;
        }

        return $this;
    }

    /**
     * @param string|Component|callable $block
     * @return $this
     */
    public function addContent($block = null)
    {
        $this->blocks[static::CONTENT] = $block;

        return $this;
    }

    /**
     * @param string|Layout $layout
     * @param string $layout_block_name
     * @return $this
     */
    public function addLayoutContent($layout, $layout_block_name = 'content')
    {
        if ($layout instanceof Layout) {
            $this->blocks[static::CONTENT] = $layout;
        } else {
            $this->blocks[static::CONTENT] = Layout::make($layout)
                ->setDefaultBlock($layout_block_name);
        }

        return $this;
    }

    /**
     * @param string|Component|callable $block
     * @return $this
     */
    public function addDebug($block = null)
    {
        if (Engine::i()->isDebug) {
            $this->blocks[static::DEBUG] = $block;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function build()
    {
        $results = [];

        foreach($this->blocks as $key => $block) {
            $result = null;

            if ($key === static::CONTENT || $key === static::DEBUG) {
                if ($key === static::DEBUG) {
                    $response = Debug::v();
                } else {
                    $response = $this->content;
                }

                if ($block instanceof Layout) {
                    Template::add($block, 'constructor');
                    $result = $block->setBlockContent($response)->render();
                    Template::clean($block);
                } else if ($block) {
                    $result = $block($response);
                } else {
                    $result = $response;
                }

            } elseif (is_string($block)) {
                $result = $block;
            } elseif ($block instanceof Component) {
                $result = $block->draw();
            } else {
                $result = $block();
            }

            $results[$key] = $result ? (string)$result : $result;
        }

        return $results;
    }

    /**
     * @param bool $v
     * @return $this
     */
    public function isJSON($v = true)
    {
        $this->isJSON = $v;

        return $this;
    }

    /**
     * @param bool $v
     * @return $this
     */
    public function isJSONP($v = true)
    {
        $this->isJSONP = $v;

        return $this;
    }

    /**
     * @param bool $v
     * @return $this
     */
    public function isXML($v = true)
    {
        $this->isXML = $v;

        return $this;
    }

    /**
     * @param bool $v
     * @return $this
     */
    public function onlyControllerResponse($v = true)
    {
        $this->isOnlyControllerResponse = $v;

        return $this;
    }

    /**
     * @param $k
     * @param $v
     * @return $this
     */
    public function header($k, $v)
    {
        $this->headers[$k] = $v;

        return $this;
    }

    /**
     * @param $arr
     * @return $this
     */
    public function headers($arr)
    {
        $this->headers = array_merge($this->headers, $arr);

        return $this;
    }

    public function compile()
    {
        if ($this->isOnlyControllerResponse) {

            $response = $this->content;

            if (($this->isJSON || $this->isJSONP) && is_array($response)) {
                $response = json_encode($response, JSON_UNESCAPED_UNICODE);
            }

            return $response;
        }

        $results = $this->build();

        if ($this->isJSON) {
            return json_encode($results, JSON_UNESCAPED_UNICODE);
        } else {
            return implode("\n\r", $results);
        }
    }

    public function render()
    {
        $this->initHeader();

        if ($this->isXML) {
            echo '<?xml version="1.0" encoding="utf-8" ?>';
        }

        echo $this->compile();
    }

    protected function initHeader()
    {
        if ($this->isXML) {
            $this->initXMLHeader();
        } elseif ($this->isJSON || $this->isJSONP) {
            $this->initJSONHeader($this->isJSONP);
        }

        if (count($this->headers) == 0) {
            return;
        }

        foreach($this->headers as $k => $v) {
            header($k . ': ' . $v);
        }
    }

    protected function initJSONHeader($isJSONP = false)
    {
        $headers = [
            'Expires' => 'Mon, 26 Jul 1997 05:00:00 GMT',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ];

        if ($isJSONP) {
            $headers['Content-type'] = 'application/javascript; charset=utf-8';
        } else {
            $headers['Content-type'] = 'application/json; charset=utf-8';
        }

        $this->headers($headers);
    }

    protected function initXMLHeader()
    {
        $this->header('Content-type', 'text/xml; charset=utf-8');
    }
}