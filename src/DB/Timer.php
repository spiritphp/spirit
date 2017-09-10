<?php

namespace Spirit\DB;

/**
 * Trait Timer
 * @package Spirit\DB
 *
 * There is logging of time
 *
 */
trait Timer {

    /**
     * Current time, ms
     * @var float
     */
    protected $timerCurrent;

    /**
     * @param null|float $old_time
     * @return float
     */
    protected function t($old_time = null)
    {
        $t = microtime(true);

        return $old_time ? $t - $old_time : $t;
    }

    /**
     * @return float
     */
    protected function tStart()
    {
        return $this->timerCurrent = $this->t();
    }

    /**
     * @return float
     */
    protected function tFinish()
    {
        return $this->t($this->timerCurrent);
    }

}