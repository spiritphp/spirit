<?php

namespace Spirit\Console\Commands;

use Spirit\Collection\Paginate;
use Spirit\Console;
use Spirit\Func\Str;
use Spirit\Structure\Command;

class Help extends Command
{

    protected $doc = [];

    protected function addToDoc($file_name, $class_name)
    {
        /**
         * @var Command|Cron $class
         */
        $class = new $class_name();

        if (!$description = $class->getDescription()) {
            $description = '-';
        }

        $commands = $class->getDescriptionCommands();

        if ($commands) {
            $r = [
                'title' => $description,
                'commands' => $commands
            ];
        } else {
            if ($file_name === 'Cron') {

                $r = [
                    'title' => $description,
                    'commands' => $class->help()
                ];

            } else {
                $r = $description;
            }
        }

        $this->doc[Str::fromCamelCase($file_name)] = $r;
    }

    protected function addAliasCommands()
    {
        $aliases = Console::getAliases();

        foreach ($aliases as $command => $alias) {

            $ext = '';
            if (is_array($alias)) {
                $_commandClass = $alias[0];

                if (isset($alias[1])) {
                    $ext = ':' . $alias[1];
                }
            } else {
                $_commandClass = $alias;
            }

            $_commandClassArr = explode('\\',$_commandClass);
            $_command = Str::fromCamelCase($_commandClassArr[count($_commandClassArr) - 1]);

            $this->doc[$command] = $_command . $ext;
        }
    }

    protected function addAppCommands()
    {
        $classes = static::findAppCommands();

        foreach ($classes as $file_name) {
            $class_name = '\App\Commands\\' . $file_name;

            $this->addToDoc($file_name, $class_name);
        }
    }

    protected function addDefaultCommands()
    {
        $classes = static::findDefaultCommands();

        foreach ($classes as $file_name) {
            $class_name = '\Spirit\Console\Commands\\' . $file_name;

            $this->addToDoc($file_name, $class_name);
        }
    }

    protected function command()
    {
        $this->addDefaultCommands();
        $this->addAppCommands();
        $this->addAliasCommands();

        ksort($this->doc);

        foreach ($this->doc as $class => $class_options) {

            if (!is_array($class_options)) {
                echo Console::textStyle($class, 'black', 'green') . ' - ' . $class_options . "\n";
                echo str_repeat('-', 50) . "\n";
                continue;
            }

            echo Console::textStyle($class, 'black', 'green') . ' - ' . $class_options['title'] . "\n";

            if (isset($class_options['commands'])) {
                foreach ($class_options['commands'] as $command => $command_options) {
                    if (!is_array($command_options)) {
                        echo "  " . $command . ' - ' . $command_options . "\n";
                        continue;
                    }

                    echo "  " . $command . ' - ' . $command_options['title'] . "\n";

                    if (isset($command_options['example'])) {
                        echo "  | Example: " . $command_options['example'] . "\n";
                    }

                    if (isset($command_options['params'])) {
                        echo "  | Params:\n";
                        foreach ($command_options['params'] as $param => $param_description) {
                            echo "  | | " . $param . ' - ' . $param_description . "\n";
                        }
                    }
                }
            }

            echo str_repeat('-', 50) . "\n";
        }
    }

}