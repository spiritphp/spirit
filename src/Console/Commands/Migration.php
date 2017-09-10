<?php

namespace Spirit\Console\Commands;

use Spirit\Console\Table as ConsoleTable;
use Spirit\Console;
use Spirit\DB;
use Spirit\DB\Schema;
use Spirit\DB\Schema\Table;
use Spirit\Engine;
use Spirit\Func;
use Spirit\Structure\Command;

class Migration extends Command
{

    protected $description = 'Migration';
    protected $descriptionCommands = [
        'reset' => 'Clean all tables',
        'rollback' => 'Remove the last migrations',
        'version' => 'Show a version',
        'list' => 'List of migration',
        'make' => [
            'title' => 'Create a migration',
            'example' => 'php spirit migration:make --table="class_name" --create="table_name" create_table_name',
            'params' => [
                '--table="table_name"' => 'Table',
                '--create="table_name"' => 'Table for creating',
                '--name="create_table_name"' => 'Filename and Class\'s name',
                'create_table_name' => 'Filename and Class\'s name',
            ]
        ]
    ];

    protected $migrations = [];
    protected $currentVersion = 0;
    protected $path;

    protected $commands = [
        'make',
        'reset',
        'rollback',
        'version',
        'list',
        'migrate',
        'status',
    ];

    protected function command()
    {
        if (!DB::hasTable('migrations')) {
            Schema::create('migrations', function (Table $table) {
                $table->serial('id')
                    ->string('migration')
                    ->integer('version')
                    ->timestamp('date')
                    ->default(DB::raw('now()'));
            });
        }

        $this->migrations = DB::table('migrations')
            ->orderDESC('id')
            ->get('migration');

        $lastVersion = 0;
        foreach($this->migrations as $item) {
            if ($item['version'] > $lastVersion) {
                $lastVersion = $item['version'];
            }
        }

        $this->currentVersion = $lastVersion;
        $this->path = Engine::dir()->migrations;

        if ($this->extCommand) {
            if (!in_array($this->extCommand, $this->commands)) {
                echo Console::textStyle('Command «' . $this->extCommand . '» is not found in Migration', 'black', 'red') . "\n";

                return;
            }

            call_user_func([$this, Func\Str::toCamelCase('command_' . $this->extCommand)]);
        } else {
            $this->commandMigrate();
        }

    }

    protected function commandStatus()
    {
        $t = ConsoleTable::make()
            ->setHeader([
                'File',
                'Status'
            ]);

        foreach(glob($this->path . "*.php") as $path) {
            Func\Date::timeStart('migrate_item');
            $name = basename($path, '.php');

            $status = Console::textStyle('NO', 'red');
            if (isset($this->migrations[$name])) {
                $status = 'YES';
            }

            $t->addRow([
                $name,
                $status
            ]);
        }

        echo $t->render();
    }

    protected function commandList()
    {
        $migrations = DB::table('migrations')
            ->orderASC('id')
            ->get();

        $t = ConsoleTable::make()
            ->setHeader([
                'Date',
                'File',
                'Version'
            ]);
        foreach($migrations as $item) {
            $t->addRow([
                date("d.m.Y H:i:s", strtotime($item['date'])),
                $item['migration'],
                $item['version']
            ]);
            //echo date("d.m.Y H:i:s",strtotime($item['date'])) . ' [' . $item['version'] . ']: ' . $item['migration'] . "\n";
        }

        echo $t->render();
    }

    protected function commandVersion()
    {
        echo "Version: " . $this->currentVersion;
    }

    protected function commandMake()
    {
        Func\Date::timeStart('make');
        echo "MAKE START\n";

        $type = 'table';
        $downAction = '';
        $upAction = '';
        $table_name = 'table_name';
        if ($t = $this->arg('create')) {
            $table_name = $t;
            $type = 'create';
            $downAction = '$table->drop();';
            $upAction = '$table->bigSerial(\'id\')->unique();';
        } elseif ($t = $this->arg('table')) {
            $table_name = $t;
        }

        if ($v = $this->arg('name')) {
            $className = Func\Str::toCamelCase($v);
        } elseif ($v = $this->getFirstBoolArg()) {
            $className = Func\Str::toCamelCase($v);
        } else {
            $className = Func\Str::toCamelCase($table_name);
        }

        $amount = 0;
        foreach(glob($this->path . "*.php") as $path) {
            if (preg_match("/" . $className . "(\d+|\.)/iu", $path)) {
                ++$amount;
            }
        }

        if ($amount > 0) {
            if ($amount < 10) {
                $amount = '0' . $amount;
            }
            $className .= $amount;
        }

        $file_name = date("Y_m_d_His") . '__' . $className . '.php';

        $migration_file = include(dirname(__FILE__) . '/migration/migration_struct.php');

        $migration_file = strtr($migration_file, [
                '{{CLASS_NAME}}' => $className,
                '{{TYPE}}' => $type,
                '{{TABLE_NAME}}' => $table_name,
                '{{ACTION_UP}}' => $upAction,
                '{{ACTION_DOWN}}' => $downAction,

            ]);

        file_put_contents($this->path . $file_name, $migration_file);
        echo $file_name . "\n";
        echo "MAKE FINISH [" . Func\Date::timeEnd('make') . "s]";
    }

    protected function commandRollback()
    {
        Func\Date::timeStart('rollback');
        echo "ROLLBACK START\n";
        echo "Ver: " . $this->currentVersion . "\n";

        $rollback_migrations = DB::table('migrations')
            ->where('version', $this->currentVersion)
            ->orderDESC('id')
            ->get('migration');

        $t = ConsoleTable::make()
            ->setHeader(['File', 'Time, s']);

        if (DB::connect()->isDriver(DB::DRIVER_POSTGRESQL)) {
            DB::beginTransaction();
        }

        foreach($rollback_migrations as $path_name => $item) {
            Func\Date::timeStart('reset_item');
            $path = $this->path . $path_name . '.php';
            if (!file_exists($path)) {
                echo Console::textStyle('File "' . $path . " not found", 'black', 'red') . "\n";
            } else {
                $this->getMigrationClass($path)
                    ->down();
            }

            DB::table('migrations')
                ->where('id', $item['id'])
                ->delete();

            $t->addRow([
                $path_name,
                Func\Date::timeEnd('reset_item')
            ]);
        }

        if (DB::connect()->isDriver(DB::DRIVER_POSTGRESQL)) {
            DB::commit();
        }

        echo $t->render();
        echo "New ver: " . ($this->currentVersion - 1) . "\n";
        echo "ROLLBACK FINISH [" . Func\Date::timeEnd('rollback') . "s]";
    }

    protected function commandReset()
    {
        Func\Date::timeStart('reset');
        echo "RESET START\n";
        echo "Ver: " . $this->currentVersion . "\n";

        $t = ConsoleTable::make()
            ->setHeader(['File', 'Time, s']);

        if (DB::connect()->isDriver(DB::DRIVER_POSTGRESQL)) {
            DB::beginTransaction();
        }

        foreach($this->migrations as $path_name => $item) {
            Func\Date::timeStart('reset_item');
            $path = $this->path . $path_name . '.php';

            if (!file_exists($path)) {
                echo Console::textStyle('File "' . $path . " not found", 'black', 'red') . "\n";
                continue;
            }
            $this->getMigrationClass($path)
                ->down();

            $t->addRow([
                $path_name,
                Func\Date::timeEnd('reset_item')
            ]);

            //echo $path_name . " [" . Func\Date::timeEnd('reset_item') . "s]\n";
        }

        Schema::table('migrations', function (Table $table) {
            $table->drop();
        });

        if (DB::connect()->isDriver(DB::DRIVER_POSTGRESQL)) {
            DB::commit();
        }

        echo $t->render();

        echo "RESET FINISH [" . Func\Date::timeEnd('reset') . "s]";
    }

    protected function commandMigrate()
    {
        Func\Date::timeStart('migrate');
        echo "MIGRATE START\n";
        echo "Ver: " . $this->currentVersion . "\n";
        $path_names = [];

        $t = ConsoleTable::make()
            ->setHeader(['File', 'Time, s']);

        if (DB::connect()->isDriver(DB::DRIVER_POSTGRESQL)) {
            DB::beginTransaction();
        }

        foreach(glob($this->path . "*.php") as $path) {
            Func\Date::timeStart('migrate_item');
            $name = basename($path, '.php');

            if (isset($this->migrations[$name])) {
                continue;
            }

            $this->getMigrationClass($path)
                ->up();

            DB::table('migrations')
                ->insert([
                    'migration' => $name,
                    'version' => $this->currentVersion
                ]);

            $path_names[] = $name;

            $t->addRow([
                $name,
                Func\Date::timeEnd('migrate_item')
            ]);
        }

        echo $t->render();

        if (count($path_names)) {
            echo "New Ver: " . ($this->currentVersion + 1) . "\n";
        } else {
            echo "There is not found for migrate\n";
        }

        if (DB::connect()->isDriver(DB::DRIVER_POSTGRESQL)) {
            DB::commit();
        }

        echo Console::textStyle('Success', 'black', 'green') . "\n";
        echo "MIGRATE FINISH [" . Func\Date::timeEnd('migrate') . "s]";
    }

    /**
     * @param $path
     * @return \Spirit\Structure\Migration
     */
    protected function getMigrationClass($path)
    {
        $basename = preg_replace("/[\d_]+__/iu", '', basename($path, ".php"));

        include_once($path);

        $className = '\Migrations\\' . $basename;

        return new $className();
    }
}