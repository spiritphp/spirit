<?php

namespace Spirit\Common\Controllers;

use Spirit\Response;
use Spirit\Structure\Controller;

class ErrorController extends Controller
{

    protected $defaultConfig = [
        'views' => [
            '404' => 'spirit::error/404',
            '403' => 'spirit::error/403',
            '503' => 'spirit::error/503',
            '500' => 'spirit::error/500',
        ]
    ];

    protected $headers = [];
    protected $data = [];

    /**
     * @param int $errorNumber
     * @param null|string $message
     * @param array $headers
     * @return null|string
     */
    public function init($errorNumber = 404, $message = null, $headers = [])
    {
        if ($headers) {
            $this->headers = $headers;
        }

        return $this->common($errorNumber, $message);
    }

    public function common($number = null, $message = null)
    {
        $this->isOnlyThis();

        if (!$number) $number = 404;

        $method = 'error' . $number;

        if (method_exists($this,$method)) {
            http_response_code($number);
            return $this->{$method}($message);
        }

        http_response_code($number);

        return $message ? $message : '';
    }

    public function error404($message = null)
    {
        $cfg = $this->cfg();

        $data = [];
        $data['message'] = $message;
        $data['back'] = $this->url();
        $data['support'] = $this->url('support');

        $tpl = $cfg['views']['404'];

        return $this->view($tpl, $data)->headers($this->headers);
    }

    public function error503($message = null)
    {
        $cfg = $this->cfg();

        $data = [];
        $data['message'] = $message;
        $data['back'] = $this->url();
        $data['support'] = $this->url('support');

        $tpl = $cfg['views']['503'];

        return $this->view($tpl, $data)->headers($this->headers);
    }

    public function error500($message = null)
    {
        $cfg = $this->cfg();

        $data = [];
        $data['message'] = $message;
        $data['back'] = $this->url();
        $data['support'] = $this->url('support');

        $tpl = $cfg['views']['500'];

        return $this->view($tpl, $data)->headers($this->headers);
    }

    public function error403($message = null)
    {
        $cfg = $this->cfg();

        $data = [];
        $data['message'] = $message;
        $data['back'] = $this->url();
        $data['support'] = $this->url('support');

        $tpl = $cfg['views']['403'];

        return $this->view($tpl, $data)->headers($this->headers);
    }

    public function error405($message = '')
    {
        return Response::make($message ? $message : 'Method Not Allowed')->headers($this->headers);
    }

    public function error429($message = '')
    {
        return Response::make($message ? $message : 'Too Many Attempts')->headers($this->headers);
    }
}