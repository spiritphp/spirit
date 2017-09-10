<?php

namespace Spirit\Console\Commands;

use Spirit\Console;
use Spirit\Structure\Command;

class Make extends Command
{
    protected $description = 'Maker';
    protected $descriptionCommands = [
        'migration' => [
            'title' => 'Create a migration',
            'example' => 'php spirit make:migration --table="class_name" --create="table_name" create_table_name',
            'params' => [
                '--table="table_name"' => 'Table',
                '--create="table_name"' => 'Table for creating',
                '--name="create_table_name"' => 'Filename and Class\'s name',
                'create_table_name' => 'Filename and Class\'s name',
            ]
        ],
    ];

    protected $commands = [
        'migration' => Migration::class,
    ];

    protected function command()
    {
        if ($this->extCommand && isset($this->commands[$this->extCommand])) {
            $className = $this->commands[$this->extCommand];
            /**
             * @var \Spirit\Structure\Command $class
             */
            $class = new $className($this->args, 'make');
            $class->exec();
        } else {
            Console::textStyle('Command not found', 'black', 'red');
        }

    }

}