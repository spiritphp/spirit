<?php

namespace Spirit\Error;

use Spirit\Common\Controllers\ErrorController;
use Spirit\Console;
use Spirit\Constructor;
use Spirit\Engine;
use Spirit\Func\Trace;
use Spirit\View;

class LogView extends LogAbstract
{

    public function render()
    {

        if ((!Engine::i()->isConsole && !Engine::i()->isDebug) || static::cfg()->pause) {

            if (static::cfg()->controllerError) {
                $class = static::cfg()->controllerError;
                $classError = new $class;
            } else {
                $classError = new ErrorController();
            }

            echo $classError->init($this->info->status_code, $this->info->message, $this->info->headers);
            return;

        } else if (!Engine::i()->isConsole) {

            http_response_code($this->info->status_code && $this->info->status_code >= 100 ? $this->info->status_code : 500);

            if ($this->info->headers && count($this->info->headers) > 0) {
                foreach($this->info->headers as $k => $v) {
                    header($k . ': ' . $v);
                }
            }

        }

        if (Engine::i()->isConsole) {
            echo "\n";
            echo Console::textStyle(' ERROR ', 'black', 'red') . "\n";
            echo $this->info->file . ':' . $this->info->line . "\n";
            echo $this->info->message . "\n";

            echo $this->tableTrace($this->info->trace) . "\n";
            echo "\n";
            return;
        }

        $data = $this->info->except(['trace', 'traceFull', 'date', 'headers']);

        Constructor::make()
            ->addView(Engine::dir()->spirit_views . 'error/trace', [
                'info' => $data,
                'trace' => $this->info->traceFull
            ])
            ->addDebug()
            ->render();
    }

}