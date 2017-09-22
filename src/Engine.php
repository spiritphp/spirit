<?php

namespace Spirit;

use Spirit\Config\Dir;
use Spirit\Func\TimeLine;
use Spirit\Response\Redirect;
use Spirit\Request\Session;
use Spirit\Route\Dispatcher;

/**
 * Class Engine
 * @package Spirit
 *
 * @property string $spirit_path
 * @property string $abs_path
 * @property string $isTesting
 * @property string $isConsole
 * @property string $isSSL
 * @property string $isDebug
 * @property string $host
 * @property string $configName
 * @property string $url
 * @property string $urlNoSeparate
 * @property string $domain
 */
class Engine
{

    /**
     * @var \Spirit\Engine[]
     */
    protected static $instances;
    protected static $instance;
    /**
     * @var \Spirit\Config
     */
    protected $cfg;
    /**
     * @var Dir
     */
    protected $dir;

    protected $includedFiles = [];
    protected $autoloadFiles = [];

    protected $controllerLog = null;

    /**
     * @var Route\Routing
     */
    protected $route;

    /**
     * @var Constructor
     */
    protected $constructor;

    protected $data = [];

    public function __construct($path = null, $conf = 'app')
    {
        static::$instances[] = static::$instance = $this;

        $this->spirit_path = __DIR__ . '/';
        $this->abs_path = $path ? $path : (__DIR__ . '/../');
        $this->configName = $conf;
    }

    public static function getTimeLine()
    {
        return TimeLine::get('spirit')->getList();
    }

    public static function getTotalTimeLine()
    {
        return TimeLine::get('spirit')->getTotal();
    }

    public static function run($path = null, $conf = 'app')
    {
        $e = new Engine($path, $conf);
        $e->start();
    }

    /**
     * @return Engine
     */
    public static function i()
    {
        return static::$instance;
    }

    /**
     * @return \Spirit\Config
     */
    public static function cfg()
    {
        return static::i()->cfg;
    }

    /**
     * @return \Spirit\Config\Dir
     */
    public static function dir()
    {
        return static::i()->dir;
    }

    /**
     * @return Route\Routing
     */
    public static function route()
    {
        return static::i()->route;
    }

    /**
     * @return string|boolean
     */
    public static function getControllerLog()
    {
        if (!static::i()->controllerLog) {
            return null;
        }

        return static::i()->controllerLog;
    }

    public static function getIncludedFiles()
    {
        return static::i()->includedFiles;
    }

    public static function getAutoloadFiles()
    {
        return static::i()->autoloadFiles;
    }

    public function __get($name)
    {
        if (!isset($this->data[$name])) {
            throw new \Exception('Not found property');
        }

        return $this->data[$name];
    }

    public function __set($name, $value)
    {
        return $this->data[$name] = $value;
    }

    public function logTimeLine($key, $description = false)
    {
        TimeLine::get('spirit', microtime(true))->log($key, $description);
    }

    public function start()
    {
        $this->debug();

        $this->initApp();
        /**/
        $this->logTimeLine('init_app');

        DB::setCfg($this->cfg->connections, $this->cfg->defaultDBConnection);

        Plugins::init();
        /**/
        $this->logTimeLine('plugins');

        if ($this->isTesting) {
            return;
        }

        if ($this->isConsole) {
            $this->console();
            return;
        }

        Request::init();
        /**/
        $this->logTimeLine('request');

        Session::init();
        /**/
        $this->logTimeLine('session');

        if ($this->cfg->pause) {
            $this->pause();
        }

        Auth::init();
        /**/
        $this->logTimeLine('init_auth');

        Event::init(Event::BEFORE_CONTROLLER);
        /**/
        $this->logTimeLine('event_before_controller');

        $route = $this->route->parse(Request::fullPath());
        /**/
        $this->logTimeLine('init_route');

        $response = $this->controller($route);
        /**/
        $this->logTimeLine('controller');

        Event::init(Event::AFTER_CONTROLLER);
        /**/
        $this->logTimeLine('event_after_controller');

        Session::complete();
        /**/
        $this->logTimeLine('session_complete');

        if ($response instanceof Redirect) {
            $response->do();
        } else {
            if (is_null($response)) {
                Error::abort(404);
            }

            $this->constructor
                ->setContent($response)
                ->render();
        }

        exit();
    }

    public function autoload($class_name)
    {
        $class_name = strtr($class_name, $this->cfg->autoloadMap);

        if (strpos($class_name, '\\') !== false) {
            $class_name = str_replace('\\', '/', $class_name);
        }

        $path = $this->abs_path . $class_name . '.php';

        $this->load($path);

        return true;
    }

    /**
     * @param $path
     * @param array $data
     * @return array|string
     */
    public function includeFile($path, $data = [])
    {
        $this->includedFiles[] = $path;

        if (is_array($data)) {
            extract($data);

            if (isset($data['data'])) {
                $data = $data['data'];
            } else {
                unset($data);
            }
        }

        return require $path;
    }

    public function handlerError($errno, $errstr = null, $errfile = null, $errline = null)
    {
        Error::make($errno, $errstr, $errfile, $errline);
    }

    /**
     * @param \Error $error
     */
    public function handlerErrorObject($error)
    {
        Error::makeFromObject($error);
    }

    /**
     * @param Constructor $constructor
     */
    public function setConstructor(Constructor $constructor)
    {
        $this->constructor = $constructor;
    }

    /**
     * @return Constructor
     */
    public function constructor()
    {
        return $this->constructor;
    }

    protected function initApp()
    {
        $this->isConsole = false;
        $this->isTesting = false;
        $this->host = '127.0.0.1';

        if (getenv('APP_ENV') === 'testing') {
            $this->isConsole = true;
            $this->isTesting = true;
        } else if (getenv('APP_ENV') === 'console') {
            $this->isConsole = true;
        } else {
            $this->host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        }

        $this->domain = $this->host;
        $this->isSSL = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $this->urlNoSeparate = ($this->isSSL ? 'https://' : 'http://') . $this->host;
        $this->url = $this->urlNoSeparate . '/';

        define('SPIRIT_KEY', str_replace('.', '_', $this->host));

        $this->initAutoload();
        $this->initErrorHandler();

        $this->dir = new Dir($this);
        $this->dir->init();

        $this->setConstructor(Constructor::make());

        $this->initConfig();

        $this->route = Route::init(null);

        foreach($this->cfg->autoloadFiles as $filepath) {
            $this->load($this->abs_path . $filepath);
        }
    }

    protected function initAutoload()
    {
        spl_autoload_register([
            $this,
            'autoload'
        ]);
    }

    protected function initErrorHandler()
    {
        if (!$this->isTesting) {
            set_error_handler([$this, 'handlerError']);
            set_exception_handler([$this, 'handlerErrorObject']);
        }
    }

    protected function initConfig()
    {
        $this->cfg = new Config();

        if (is_callable($this->configName)) {
            call_user_func($this->configName, $this->cfg, $this->constructor());
        } else {
            $this->cfg->loadConfig($this->abs_path . 'config/' . $this->configName . '.php', $this->cfg, $this->constructor());
        }

        date_default_timezone_set($this->cfg->timezone);
    }

    /**
     * Закрыт ли сайт не тех.работы
     */
    protected function pause()
    {
        if (Engine::i()->isConsole) return;

        if ($pauseOpen = $this->cfg->pauseOpen) {

            if (!$open = Session::get('pause_open')) {
                $open = Request::get('pause_open');
            }

            if ($open && hash_equals($open, $pauseOpen)) {
                Session::set('pause_open', $open);
                return;
            }

        }

        Error::pause();
    }

    protected function console()
    {
        Console::make($_SERVER['argv'])->run();
    }

    protected function debug()
    {
        error_reporting(E_ALL);

        $this->isDebug = isDebug();

        ini_set('display_errors', $this->isDebug ? 1 : 0);
    }

    /**
     * @param Route\Current $route
     * @return null|Response|Redirect
     */
    protected function controller($route)
    {
        $this->controllerLog = [];

        if (!$route) {
            return null;
        }

        $this->controllerLog['route'] = $route->config;

        Func\Date::timeStart('controller');

        $dispatcher = Dispatcher::make($route);

        $response = $dispatcher->response();
        $this->controllerLog = array_merge($this->controllerLog, $dispatcher->logs());

        $t = Func\Date::timeEnd('controller');

        $this->controllerLog['time'] = $t;

        return $response;
    }

    /**
     * Подключение файла
     *
     * @param $path
     */
    protected function load($path)
    {
        $t = microtime(true);

        $this->includeFile($path);

        $this->autoloadFiles[] = [
            'path' => $path,
            'time' => (microtime(true) - $t)
        ];
    }
}