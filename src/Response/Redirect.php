<?php

namespace Spirit\Response;

use Spirit\Engine;
use Spirit\Event;
use Spirit\Request;
use Spirit\Request\Session;
use Spirit\Request\URL;
use Spirit\Services\Validator\ErrorMessages;
use Spirit\Structure\Arrayable;

class Redirect
{

    const TYPE_BACK = 'back';
    const TYPE_RELOAD = 'reload';

    protected $redirect = null;
    protected $isPost = false;
    protected $params = [];

    public function __construct($redirect = null, $params = [])
    {
        $this->redirect = $redirect;
        $this->params = $params;
    }

    public static function make($redirect = null, $params = [])
    {
        return new static($redirect, $params);
    }

    public function post()
    {
        $this->isPost = true;
        return $this;
    }

    public function do()
    {
        if ($this->redirect === static::TYPE_BACK) {
            $url = URL::back();

            if (!$url) {
                $url = Engine::i()->url;
            }
        } elseif ($this->redirect === static::TYPE_RELOAD) {
            $url = URL::href();
        } elseif (strpos($this->redirect, 'http') !== false) {
            $url = $this->redirect;
        } else {
            $path = $this->redirect;
            if (strpos($path, '/') === 0) {
                $path = substr($path, 1);
            }
            $url = Engine::i()->url . $path;
        }

        if ($this->isPost) {
            $form = $this->getPostForm($url, $this->params);
            echo $form;
        } else {
            $query = '';
            if ($this->params) {
                if (is_array($this->params) && count($this->params)) {
                    $p = (strpos($url, '?') === false ? '?' : '&') . http_build_query($this->params);
                } else {
                    $p = $this->params;
                }
                $query = (strpos($url, '?') === false ? '?' : '&') . $p;
            }

            $url_redirect = $url . $query;

            header('Location: ' . $url_redirect);
        }

    }

    public function send()
    {
        Session::complete();
        $this->do();
        exit();
    }

    public function with($key, $value)
    {
        Session::once($key, $value);

        return $this;
    }

    public function withInputs($inputs = null)
    {
        Session::once('_inputs', $inputs ?: Request::all());

        return $this;
    }

    public function withErrors($errors)
    {
        if ($errors instanceof Arrayable) {
            $errors = $errors->toArray();
        }

        Session::once('_errors', $errors);

        return $this;
    }

    protected function getPostForm($url, $params = [])
    {
        if (!is_array($params)) $params = [$params];

        $form_id = 'redirect_form_' . uniqid();
        $html = [];
        $html[] = '<form method="POST" action="' . $url . '" id="' . $form_id . '" charset="UTF-8">';

        foreach ($params as $key => $value) {
            $html[] = '<input type="hidden" name="' . $key . '" value="' . $value . '" />';
        }

        $html[] = '<input type="submit" value="Sending" id="' . $form_id . '_button" />';

        $html[] = '</form>';

        $html[] = '<script type="text/javascript">var b = document.getElementById("' . $form_id . '_button"); if (b && b.parentNode) {b.parentNode.removeChild(b)}; document.getElementById("' . $form_id . '").submit();</script>';

        return implode('', $html);
    }

    public function home()
    {
        $this->redirect = '/';
        return $this;
    }

    public function back()
    {
        $this->redirect = self::TYPE_BACK;
        return $this;
    }

    public function reload()
    {
        $this->redirect = self::TYPE_RELOAD;
        return $this;
    }

    public static function to($to = null, $params = [])
    {
        return static::make($to, $params);
    }

    public function route($route_name, $params = [])
    {
        $this->redirect = URL::route($route_name, $params);
        return $this;
    }

    /**
     * Отложенный редирект
     *
     * @param $path
     * @param $params
     */
    public static function after($path, $params = [])
    {
        Event::add(
            Event::AFTER_CONTROLLER,
            function () use ($path, $params) {
                Redirect::to($path)->send();
            },
            'after_redirect'
        );
    }
}