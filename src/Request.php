<?php

namespace Spirit;

use Spirit\Request\CookieVariables;
use Spirit\Request\FileVariables;
use Spirit\Request\HeaderVariables;
use Spirit\Request\RequestProvider;
use Spirit\Request\UploadedFile;
use Spirit\Request\Variables;

/**
 * Class Request
 * @package Spirit
 *
 *
 * @method static void imitationMethod($method)
 * @method static string fullPath()
 * @method static array|string getPath($i = null)
 * @method static mixed|Variables get($key = null, $default = null)
 * @method static mixed|Variables query($key = null, $default = null)
 * @method static mixed|Variables post($key = null, $default = null)
 * @method static mixed|HeaderVariables header($key = null, $default = null)
 * @method static mixed|Variables server($key = null, $default = null)
 * @method static mixed|CookieVariables cookie($key = null, $default = null)
 * @method static null|UploadedFile|FileVariables file($key = null, $default = null)
 * @method static bool has(...$keys)
 * @method static array only(...$keys)
 * @method static array except(...$keys)
 * @method static void forget(...$keys)
 * @method static bool token()
 * @method static bool isPOST()
 * @method static bool isGET()
 * @method static bool isPUT()
 * @method static bool isDELETE()
 * @method static bool isAjax()
 * @method static string getMethod()
 * @method static bool isMethod($method)
 * @method static array all()
 * @method static string|null getJSONPCallback()
 */
class Request {

    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';

    /**
     * @var RequestProvider
     */
    public static $requestProviderClass = RequestProvider::class;

    protected static $instance;

    public static function getInstance()
    {
        if (static::$instance) return static::$instance;

        return static::$instance = new static(new static::$requestProviderClass());
    }

    public static function init($post = null, $get = null, $server = null, $files = null)
    {
        $className = static::$requestProviderClass;

        static::$instance = new static(
            new $className($post, $get, $server, $files)
        );
    }

    public static function clean()
    {
        static::$instance =  null;
    }

    /**
     * @var RequestProvider
     */
    protected $requestProvider;

    public function __construct($requestProvider)
    {
        $this->requestProvider = $requestProvider;
    }

    public function requestProvider()
    {
        return $this->requestProvider;
    }

    public function __call($name, $arguments)
    {
        return $this->requestProvider->{$name}(...$arguments);
    }

    public static function __callStatic($name, $arguments)
    {
        return static::getInstance()->{$name}(...$arguments);
    }
}
