<?php

namespace Spirit\Structure;

use Spirit\Engine;
use Spirit\Services\Admin;
use Spirit\Error;
use Spirit\Response\FE;
use Spirit\Request;
use Spirit\Response;

abstract class Controller extends Basic implements \JsonSerializable
{
    protected function title($t)
    {
        FE::setTitleDescription($t);
    }

    protected function isOnlyThis()
    {
        Engine::i()
            ->constructor()
            ->onlyControllerResponse();

        return $this;
    }

    protected function response($content)
    {
        return Response::make($content);
    }

    protected function xml($xml)
    {
        Engine::i()
            ->constructor()
            ->onlyControllerResponse()
            ->isXML();

        return $this->response($xml);
    }

    /**
     * @param $data
     * @return Response
     */
    protected function json($data)
    {
        if ($callback = Request::getJSONPCallback()) {
            Engine::i()
                ->constructor()
                ->onlyControllerResponse()
                ->isJSONP();

            return $this->response($callback . '(' . json_encode($data, JSON_UNESCAPED_UNICODE) . ')');
        }

        Engine::i()
            ->constructor()
            ->onlyControllerResponse()
            ->isJSON();

        return $this->response(json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    protected function abort($errorNumber = 404, $message = null)
    {
        Error::abort($errorNumber, $message);
    }

    /**
     * @param null|string $view
     * @param null $data
     * @return Response
     */
    public function view($view = null, $data = null)
    {
        return $this->response(parent::view($view, $data));
    }

    protected function addCss($name)
    {
        FE::addCss($name);
        return $this;
    }

    protected function addJs($name)
    {
        FE::addJs($name);
        return $this;
    }

    protected function url($dir = false)
    {
        return Request\URL::make($dir);
    }

    protected function reload()
    {
        return Response\Redirect::reload();
    }

    protected function redirect($to = false, $params = false)
    {
        return Response\Redirect::to($to, $params);
    }

    public function __toString()
    {
        return get_called_class();
    }

    public function jsonSerialize()
    {
        return get_called_class();
    }
}
