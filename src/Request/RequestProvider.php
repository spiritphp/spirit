<?php

namespace Spirit\Request;

use Spirit\Engine;
use Spirit\Request;
use Spirit\Services\Validator\ErrorMessages;

/**
 * Class RequestProvider
 * @package Spirit\Request
 *
 * @method array only(...$args)
 * @method array all()
 */
class RequestProvider
{

    protected $method = null;
    protected $isAjax = null;

    /**
     * @var CookieVariables
     */
    public $cookie;

    /**
     * @var HeaderVariables
     */
    public $headers;

    /**
     * @var FileVariables
     */
    public $files;

    /**
     * @var Variables
     */
    public $server;

    /**
     * @var Variables
     */
    public $post;

    /**
     * @var Variables
     */
    public $query;

    protected $pathArr = [];
    protected $fullPath = '';

    public function __construct($post = null, $query = null, $server = null, $files = null, $cookie = null)
    {
        $this->post = Variables::make($post ? $post : $_POST);
        $this->query = Variables::make($query ? $query : $_GET);
        $this->server = Variables::make($server ? $server : $_SERVER);
        $this->files = FileVariables::make($files ? $files : $_FILES);
        $this->headers = HeaderVariables::make($server ? $server : $_SERVER);
        $this->cookie = CookieVariables::make($cookie ? $cookie : $_COOKIE);

        $this->initPath();
    }

    public function __call($name, $arguments)
    {
        if ($this->isPOST()) {
            return $this->post->{$name}(...$arguments);
        }

        return $this->query->{$name}(...$arguments);
    }

    public function imitationMethod($method)
    {
        if (!Engine::i()->isConsole) {
            return;
        }

        $this->method = $method;
    }

    /**
     * @param $k
     * @return UploadedFile|null|FileVariables
     */
    public function file($k = null)
    {
        return $this->files->get($k);
    }

    public function query($k = null, $default = null)
    {
        return $this->query->get($k, $default);
    }

    public function post($k = null, $default = null)
    {
        return $this->post->get($k, $default);
    }

    /**
     * @param $k
     * @param $default
     * @return mixed|null|HeaderVariables
     */
    public function header($k = null, $default = null)
    {
        return $this->headers->get($k, $default);
    }

    /**
     * @param $k
     * @param $default
     * @return mixed|null|CookieVariables
     */
    public function cookie($k = null, $default = null)
    {
        return $this->cookie->get($k, $default);
    }

    public function server($k = null, $default = null)
    {
        return $this->server->get($k, $default);
    }

    public function get($k = null, $default = null)
    {
        if ($this->isPOST()) {
            return $this->post->get($k, $default);
        }

        return $this->query->get($k, $default);
    }

    public function getJSONPCallback()
    {
        if (!$callback = $this->get('callback'))
            return null;

        if (!preg_match("/^[a-z][a-z\d\-_]+$/ius", $callback))
            return null;

        return $callback;
    }

    public function isPOST()
    {
        return $this->isMethod(Request::METHOD_POST);
    }

    public function isGET()
    {
        return $this->isMethod(Request::METHOD_GET);
    }

    public function isPUT()
    {
        return $this->isMethod(Request::METHOD_PUT);
    }

    public function isDELETE()
    {
        return $this->isMethod(Request::METHOD_DELETE);
    }

    public function isAjax()
    {
        if (!is_null($this->isAjax)) {
            return $this->isAjax;
        }

        $this->isAjax = false;

        if ($this->server->get('X_REQUESTED_WITH')) {
            $this->isAjax = true;
        } elseif ($this->post->get('_ajax') === 1) {
            $this->isAjax = true;
        }

        return $this->isAjax;
    }

    public function getMethod()
    {
        if (!is_null($this->method))
            return $this->method;

        $method = strtoupper($this->server->get('REQUEST_METHOD', 'GET'));

        if ($method === 'POST') {
            if ($m = $this->headers->get('X-HTTP-METHOD-OVERRIDE')) {
                $method = strtoupper($m);
            } else if (isset($_REQUEST['_method'])) {
                $method = strtoupper($_REQUEST['_method']);
            }
        }

        return $this->method = $method;
    }

    public function isMethod($method)
    {
        return strtoupper($method) === $this->getMethod();
    }

    public function fullPath()
    {
        return $this->fullPath;
    }

    public function getPath($i = null)
    {
        if (!is_null($i)) {
            return isset($this->pathArr[$i]) ? $this->pathArr[$i] : null;
        }

        return $this->pathArr;
    }

    protected function initPath()
    {
        if (!$uri = $this->server->get('REQUEST_URI')) {
            return;
        }

        $urlParse = parse_url($uri);

        if (!isset($urlParse['path'])) {
            return;
        }

        $path = trim(strtr(strip_tags($urlParse['path']), [
            '"' => '',
            "'" => '',
            'index.php' => ''
        ]));

        if (!$path || $path === '') {
            return;
        }

        if ($path[0] === '/') {
            $path = substr($path, 1);
        }

        $path_arr = explode("/", $path);

        foreach($path_arr as $item) {
            if (!trim($item)) {
                continue;
            }

            $this->pathArr[] = $item;
        }

        $this->fullPath = implode('/', $this->pathArr);

        return;
    }

    public function old($key)
    {
        $inputs = Session::get('_inputs');

        if (!$inputs || !is_array($inputs)) {
            return null;
        }

        return isset($inputs[$key]) ? $inputs[$key] : null;
    }

    /**
     * @return ErrorMessages
     */
    public function errors()
    {
        $errors = Session::get('_errors');

        if (!$errors || !is_array($errors)) {
            $errors = [];
        }

        return new ErrorMessages($errors);
    }

    public function token()
    {
        if ($token = $this->get('_token')) {
            return $token;
        }

        return $this->header('X-CSRF-TOKEN');
    }
}