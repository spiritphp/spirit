<?php

namespace Spirit\Response;

use Spirit\Engine;
use Spirit\Event;
use Spirit\Request\Session;
use Spirit\Request\URL;

class Redirect
{

    const TYPE_BACK = 'back';
    const TYPE_RELOAD = 'reload';

    protected $redirect;
    protected $isPost = false;
    protected $params = [];

    public function __construct($redirect, $params = [])
    {
        $this->redirect = $redirect;
        $this->params = $params;
    }

    public static function make($redirect, $params = [])
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

    public static function home($params = [])
    {
        return static::to('/', $params);
    }

    public static function back($params = [])
    {
        return static::to(self::TYPE_BACK, $params);
    }

    public static function reload($params = [])
    {
        return static::to(self::TYPE_RELOAD, $params);
    }

    public static function to($to = false, $params = [])
    {
        return new Redirect($to, $params);
    }

    public static function route($route_name, $params)
    {
        return static::to(URL::route($route_name, $params));
    }

    public static function methodPost($to, $params = [])
    {
        return static::make($to, $params)
            ->post();
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