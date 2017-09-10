<?php

namespace Spirit\Console\Commands;

use Spirit\Config\Dotenv;
use Spirit\Console;
use Spirit\Engine;
use Spirit\Services\Mail;
use Spirit\Structure\Command;

class AppKey extends Command
{
    protected $description = 'Generate an APP_KEY';

    protected function command()
    {
        $env = Dotenv::make();

        if (!$env->exist()) {
            echo Console::textStyle('File «.env» is not found', 'black', 'red') . "\n";
            return;
        }

        if ($env->getEnvValue('APP_KEY')) {
            echo Console::textStyle('APP_ENV already exists', 'black', 'yellow') . "\n";
            return;
        }

        $env->set('APP_KEY', md5(uniqid() . SPIRIT_KEY));
        $env->save();

        echo Console::textStyle('Generated APP_KEY writes to «.env»', 'black', 'green') . "\n";
    }

}