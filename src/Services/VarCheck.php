<?php

namespace Spirit\Services;

use Spirit\Cache;

/**
 * Class VarCheck
 * @package Spirit\Services
 *
 * @property integer $amount
 * @property integer $last_time
 *
 * @property boolean $fix
 */
class VarCheck
{
    const KEY_AMOUNT = 'a';
    const KEY_LAST_TIME = 't';

    /**
     * @param $val
     * @param string $type
     * @param integer $lifeTime
     * @return static
     */
    public static function make($val, $type = 'var_check', $lifeTime = 7200)
    {
        return new static($val, $type, $lifeTime);
    }

    protected $info = [];
    protected $key;
    protected $val;
    protected $lifeTime = 7200;
    protected $type = 'var_check';

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * VarCheck constructor.
     * @param $val
     * @param string $type
     * @param integer $lifeTime
     */
    public function __construct($val, $type = null, $lifeTime = 7200)
    {
        $this->key = sha1($val);
        $this->val = $val;
        $this->lifeTime = $lifeTime;
        $this->type = $type;

        $this->cache = Cache::store('file')->dir('var_check/' . ($this->type ? $this->type : 'default'));

        $this->info = $this->getInfo();
    }

    protected function getInfo()
    {
        $d = $this->cache->get($this->key);

        return $d ? $d : [];
    }

    public function __get($key)
    {
        if (isset($this->info[$key])) {
            return $this->info[$key];
        }

        return null;
    }

    /**
     * Ограничение двух запросов за определенное время
     *
     * @param int $sec
     * @return bool
     */
    public function checkTimeDelay($sec = 10)
    {
        return ((time() - $this->last_time) >= $sec);
    }

    /**
     * Время ожидания для следующего запроса
     *
     * @param int $sec
     * @return int
     */
    public function getTimeWait($sec = 10)
    {
        $t = $sec - (time() - $this->last_time);

        return $t > 0 ? $t : 0;
    }

    /**
     * Ограничение запросов по кол-ву
     *
     * @param int $amount
     * @return bool
     */
    public function checkAmountLimit($amount = 10)
    {
        return ($this->amount < $amount);
    }

    /**
     * Очистка
     */
    public function clean()
    {
        $this->cache->forget($this->key);
    }

    /**
     * Логировать
     */
    public function log()
    {
        if (count($this->info) == 0) {
            $this->info = [
                self::KEY_AMOUNT => 0,
                self::KEY_LAST_TIME => null
            ];
        }

        ++$this->info[self::KEY_AMOUNT];
        $this->info[self::KEY_LAST_TIME] = time();

        $this->cache->put($this->key, $this->info, $this->lifeTime);
    }
}