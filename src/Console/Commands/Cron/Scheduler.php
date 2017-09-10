<?php

namespace Spirit\Console\Commands\Cron;

use Spirit\Engine;
use Spirit\Func\Num;
use Spirit\Func\Str;

class Scheduler
{

    /**
     * @param $name
     * @param $description
     * @return Scheduler
     */
    public static function make($name, $description = null)
    {
        return new static($name, $description);
    }

    protected $name;
    protected $description;
    protected $callback;
    protected $logPath;

    protected $withoutOverlapping = false;
    protected $overlappingFileLock = false;
    protected $overlappingAmountTry = 10;

    protected $whenCron;
    protected $whenDailyAt;
    protected $whenEveryMinute;

    public function __construct($name, $description = null)
    {
        $this->name = $name;
        $this->description = $description;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDescription()
    {
        $d = $this->description;

        if ($this->whenCron) {
            $d .= ' (' . $this->whenCron . ')';
        } elseif ($this->whenDailyAt) {
            $d .= ' (Everyday in ' . $this->whenDailyAt . ')';
        } elseif ($this->whenEveryMinute) {
            $d .= ' (Every ' . $this->whenEveryMinute . ' min)';
        }

        return trim($d);
    }

    public function call($callback)
    {
        $this->callback = $callback;
        return $this;
    }

    protected function getFileLockPath()
    {
        if (strpos($this->overlappingFileLock, '/') !== 0) {
            $file_lock = Engine::dir()->logs . 'scheduler_lock/' . $this->overlappingFileLock;
        } else {
            $file_lock = $this->overlappingFileLock;
        }

        $dir = dirname($file_lock);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $file_lock .= '.json';

        return $file_lock;
    }

    protected function isRun()
    {
        if (!$this->withoutOverlapping) return false;

        $file_lock = $this->getFileLockPath();

        if (!file_exists($file_lock)) return false;

        $lock = json_decode(file_get_contents($file_lock), 1);
        $amount_try = $lock['amount_try'];

        if ($amount_try >= $this->overlappingAmountTry) {
            unlink($file_lock);
            return false;
        }

        ++$lock['amount_try'];

        file_put_contents($file_lock, json_encode($lock));

        return true;
    }

    protected function lockStart()
    {
        if (!$this->withoutOverlapping) return;

        $file_lock = $this->getFileLockPath();
        file_put_contents($file_lock, json_encode(['amount_try' => 0]));
    }

    protected function lockEnd()
    {
        if (!$this->withoutOverlapping) return;

        $file_lock = $this->getFileLockPath();
        if (file_exists($file_lock)) {
            unlink($file_lock);
        }
    }

    protected function log($t)
    {
        if (!$this->logPath) return;

        if (strpos($this->logPath, '/') !== 0) {
            $this->logPath = Engine::dir()->logs . 'scheduler/' . $this->logPath;
        }

        $dir = dirname($this->logPath);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!pathinfo($this->logPath, PATHINFO_EXTENSION)) {
            $this->logPath .= '.log';
        }

        if (file_exists($this->logPath)) {
            $size = filesize($this->logPath);

            if ($size > 100000) {
                unlink($this->logPath);
            }
        }

        file_put_contents($this->logPath, date("Y-m-d H:i:s") . ' [' . round($t, 6) . ']' . "\n", FILE_APPEND);
    }

    protected function callCallback()
    {
        $result = call_user_func($this->callback);

        return $result ? $result : true;
    }

    /**
     * @param $logPath
     * @return $this
     */
    public function withLog($logPath = false)
    {
        $this->logPath = $logPath ? $logPath : $this->name;

        return $this;
    }

    /**
     * @param string|boolean $file_lock
     * @param integer $amountTry
     * @return $this
     */
    public function withoutOverlapping($amountTry = 10, $file_lock = false)
    {
        $this->withoutOverlapping = true;
        $this->overlappingFileLock = $file_lock ? $file_lock : $this->name;
        $this->overlappingAmountTry = $amountTry;

        return $this;
    }

    public function pretty()
    {
        return $this->withLog()->withoutOverlapping();
    }

    /**
     * @param int $minute
     * @return $this
     */
    public function everyMinute($minute = 1)
    {
        $this->whenEveryMinute = $minute;
        return $this;
    }

    /**
     * @param $date - date(H:i)
     * @return $this
     */
    public function dailyAt($date)
    {
        $this->whenDailyAt = $date;
        return $this;
    }

    /**
     * @param $cron
     * @return $this
     */
    public function cron($cron)
    {
        $this->whenCron = $cron;
        return $this;
    }

    /**
     * @return bool
     */
    public function check()
    {
        if ($this->whenCron) {
            return $this->checkCron();
        }

        if ($this->whenDailyAt) {
            return $this->checkDailyAt();
        }

        if ($this->whenEveryMinute) {
            return $this->checkEveryMinute();
        }

        return true;
    }

    protected function checkDailyAt()
    {
        if (date('H:i') !== $this->whenDailyAt) return false;

        return true;
    }

    protected function checkEveryMinute()
    {
        $allMinutes = round(time() / 60);

        if ($allMinutes % $this->whenEveryMinute !== 0) return false;

        return true;
    }

    protected function checkCron()
    {
        $cron = $this->whenCron;

        if ($cron === '* * * * *' || $cron === '*/1 * * * *') {
            return true;
        }

        $cronArr = explode(' ', $cron);

        $cronCheck = [
            'w' => $cronArr[4],
            'n' => $cronArr[3],
            'j' => $cronArr[2],
            'H' => $cronArr[1],
            'i' => $cronArr[0],
        ];

        $result = true;
        foreach ($cronCheck as $k => $v) {
            if ($v === '*') {
                continue;
            }

            $currentVal = (int)date($k);

            if (strpos($v, '*/') === 0) {
                $perVal = str_replace('*/', '', $v);

                if (
                    (60 % $perVal !== 0 && $currentVal === 0) ||
                    ($currentVal % $perVal !== 0)
                ) {
                    $result = false;
                }
            } elseif (strpos($v, '-') !== false) {
                $vArr = explode(',', $v);
                foreach ($vArr as $value) {
                    $value = explode('-', $value);
                    if (count($value) == 1 && (int)$value[0] !== $currentVal) {
                        $result = false;
                        break;
                    } elseif ($value[0] > $currentVal || $currentVal > $value[1]) {
                        $result = false;
                        break;
                    }
                }

            } elseif (strpos($v, ',') !== false) {
                $vArr = explode(',', $v);

                if (!in_array($currentVal, $vArr)) {
                    $result = false;
                }
            } else {
                if ($currentVal !== (int)$v) {
                    $result = false;
                }
            }

            if (!$result) {
                break;
            }
        }

        return $result;
    }

    public function exec()
    {
        if ($this->isRun()) return false;

        $this->lockStart();

        $t = microtime(true);
        $result = $this->callCallback();
        $t = microtime(true) - $t;

        $this->log($t);

        $this->lockEnd();

        return $result;
    }

    public function execBg()
    {
        exec('php ' . Engine::i()->abs_path . 'spirit cron ' . $this->name . ' >> /dev/null 2>&1 &');
    }
}