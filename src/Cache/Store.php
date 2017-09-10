<?php

namespace Spirit\Cache;

abstract class Store
{

    public static function make($config)
    {
        return new static($config);
    }

    /**
     * @var array
     */
    protected $config;

    public $statPut = [];
    public $statGet = [];

    public function __construct($config)
    {
        $this->config = $config;
        $this->init();
    }

    public function getStatPut()
    {
        return $this->statPut;
    }

    public function getStatGet()
    {
        return $this->statGet;
    }

    protected function toTimestamp($minutes)
    {
        return $minutes > 0 ? (time() + $minutes * 60) : 0;
    }

    abstract protected function init();

    /**
     * @param $key
     * @return bool
     */
    abstract public function has($key);

    /**
     * @param $key
     * @return mixed
     */
    abstract public function get($key);

    /**
     * @param $key
     * @param $value
     * @param null $exp
     * @return void
     */
    abstract public function put($key, $value, $exp = null);

    /**
     * @param $key
     * @param $value
     * @return void
     */
    abstract public function forever($key, $value);

    /**
     * @param $key
     * @return mixed
     */
    abstract public function pull($key);

    /**
     * @param $key
     * @return void
     */
    abstract public function forget($key);

    public function flush()
    {

    }
}