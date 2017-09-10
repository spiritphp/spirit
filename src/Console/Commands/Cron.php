<?php

namespace Spirit\Console\Commands;

use Spirit\Console\Commands\Cron\Scheduler;
use Spirit\Console;
use Spirit\Structure\Command;

/**
 * Class Cron
 * @package Spirit\Console\Commands
 *
 * Запустить
 * crontab -e
 * * * * * * php /path/to/spirit cron >> /dev/null 2>&1
 *
 */
abstract class Cron extends Command
{

    protected $description = 'Run cron';

    /**
     * @var Scheduler[]
     */
    protected $schedulers = [

    ];

    /**
     * @param $name
     * @param $description
     * @return Scheduler
     */
    protected function add($name, $description = null)
    {
        return $this->schedulers[$name] = Scheduler::make($name, $description);
    }

    /**
     * $this->add('cron_name')->pretty()->call(function(){})->cron('* * * * *');
     *
     * @return mixed
     */
    abstract protected function schedule();

    public function help()
    {
        $this->schedule();

        $commands = [];
        foreach ($this->schedulers as $scheduler) {
            $commands[$scheduler->getName()] = $scheduler->getDescription();
        }

        return $commands;
    }

    final protected function command()
    {
        $this->schedule();

        if ($name = $this->getFirstBoolArg()) {

            if (isset($this->schedulers[$name])) {
                $this->schedulers[$name]->exec();
            }

            return;
        }

        foreach ($this->schedulers as $scheduler) {
            if ($scheduler->check()) {
                echo Console::textStyle('Start ' . $scheduler->getName(), 'black', 'green') . "\n";

                $scheduler->execBg();
            }
        }
    }

}