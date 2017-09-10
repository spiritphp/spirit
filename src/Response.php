<?php

namespace Spirit;
use Spirit\Structure\Arrayable;
use Spirit\Structure\Jsonable;

/**
 * Class Response
 * @package Spirit
 *
 * @method Response onlyControllerResponse($v = true)
 * @method Response isJSON($v = true)
 * @method Response isJSONP($v = true)
 * @method Response isXML($v = true)
 * @method View headers($arr)
 * @method View header($k, $v)
 */
class Response {

    /**
     * @param View|String|null $content
     * @return static
     */
    public static function make($content = null)
    {
        return new static($content);
    }

    /**
     * @var View|String|null
     */
    protected $content;

    /**
     * Response constructor.
     * @param View|String|null $content
     */
    public function __construct($content = null)
    {
        $this->content = $content;
    }

    public function __call($method, $arg)
    {
        Engine::i()->constructor()->{$method}(...$arg);

        return $this;
    }

    public function toString()
    {
        if ($this->content instanceof Jsonable) {
            $this->isJSON();
            return $this->content->toJson(JSON_UNESCAPED_UNICODE);
        } elseif($this->content instanceof Arrayable) {
            $this->isJSON();
            return json_encode($this->content->toArray(), JSON_UNESCAPED_UNICODE);
        } else if (is_array($this->content)) {
            $this->isJSON();
            return json_encode($this->content, JSON_UNESCAPED_UNICODE);
        } else if($this->content instanceof View) {
            return $this->content->render();
        }

        return $this->content;
    }

    public function __toString()
    {
        return (string)$this->toString();
    }
}