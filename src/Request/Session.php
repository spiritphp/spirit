<?php

namespace Spirit\Request;

use Spirit\Engine;
use Spirit\Request\Session\Box;
use Spirit\Request\Session\Storage;

/**
 * Class Session
 * @package Spirit\Response
 *
 * @method static mixed get($k,$default = null)
 * @method static void set($k,$v = null)
 * @method static void forget(...$keys)
 * @method static void complete()
 * @method static Storage storage($k)
 * @method static array except(...$keys)
 * @method static array only(...$keys)
 * @method static bool has(...$keys)
 * @method static array all()
 * @method static string token()
 * @method static array once($k,$v)
 */
class Session
{
    protected static $instance;

    public static function getInstance()
    {
        if (static::$instance) return static::$instance;

        return static::$instance = new static();
    }

    public static function init()
    {
        static::$instance = new static();
    }

    public static function initTest()
    {
        static::$instance = new static(true);
    }

    protected $isSimpleArray = false;

    /**
     * @var Box
     */
    protected $box;

    public function __construct($isSimpleArray = false)
    {
        $this->isSimpleArray = $isSimpleArray;

        ini_set("session.entropy_length", 32);
        ini_set("session.hash_function",'sha1');
        ini_set("session.gc_divisor", 100);
        ini_set('session.gc_probability', 1);

        $handlerClass = Engine::cfg()->sessionHandlerClass;

        session_set_save_handler(new $handlerClass(), true);

        session_name('spirit_t');

        if (!$this->isSimpleArray) {
            session_start();
        }

        $this->box = new Box($this->isSimpleArray ? [] : $_SESSION);
    }

    public function write()
    {
        $data = $this->box->toArray();
        unset($data['_clean']);

        $this->box = new Box($data);

        if (!$this->isSimpleArray) {
            $_SESSION = $data;
        }

        return true;
    }

    public function __call($name, $arguments)
    {
        if ($name === 'complete') {
            return $this->write();
        }

        return $this->box->{$name}(...$arguments);
    }

    public static function __callStatic($name, $arguments)
    {
        return static::getInstance()->{$name}(...$arguments);
    }
}