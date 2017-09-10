<?php

namespace Spirit\Func;

class TimeLine
{

    /**
     * @var TimeLine[]
     */
    protected static $instances = [];

    /**
     * @param bool|false $key
     * @param mixed $startTime
     * @return TimeLine
     */
    public static function get($key = false, $startTime = false)
    {
        if ($key) {
            if (!isset(static::$instances[$key])) {
                static::$instances[$key] = new TimeLine($key, $startTime);
            }

            return static::$instances[$key];
        } else {
            return new TimeLine(false, $startTime);
        }
    }

    public static function logFor($keyType, $key = false, $description = false)
    {
        static::get($keyType)->log($key, $description);
    }

    public static function getListFor($keyType)
    {
        static::get($keyType)->getList();
    }

    protected $key;
    protected $timeLine = [];
    protected $lastTime = 0;
    protected $periodSum = 0;

    public function __construct($key = false, $startTime = false)
    {
        $this->key = $key;

        if ($startTime) {
            $this->log('start', false, $startTime);
        }
    }

    public function log($key = false, $description = false, $microtime = false)
    {
        $t = is_numeric($microtime) ? $microtime : microtime(true);

        if (count($this->timeLine) == 0) {
            $period = 0;
            $sum = 0;
        } else {
            $period = $t - $this->lastTime;
            $this->periodSum += $period;
            $sum = $this->periodSum;
        }

        $this->lastTime = $t;

        $this->timeLine[] = array(
            'time' => $t,
            'period' => $period,
            'key' => $key,
            'description' => $description,
            'sum' => $sum
        );
    }

    public function getList()
    {
        return $this->timeLine;
    }

    public function getTotal()
    {
        return $this->periodSum;
    }
}