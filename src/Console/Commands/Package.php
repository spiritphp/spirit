<?php

namespace Spirit\Console\Commands;

use Spirit\Console\Table as ConsoleTable;
use Spirit\Console;
use Spirit\Structure\Command;

class Package extends Command
{
    protected $description = 'Spirit Package Manager';

    protected $descriptionCommands = [
        'list' => [
            'title' => 'Show list of packages',
            'example' => 'php spirit package:list'
        ],
        'install' => [
            'title' => 'Install package',
            'example' => 'php spirit package:install package_name',
            'params' => [
                'package_name' => 'Name of package'
            ]
        ],
    ];

    /**
     * @param $search
     * @return null|\Spirit\Structure\Package
     */
    protected function findPackage($search)
    {
        $findPackage = null;
        foreach($this->cfg()->packages as $package) {
            if (hash_equals($search, $package::name())) {
                $findPackage = $package;
                break;
            }
        }

        return $findPackage;
    }

    protected function command()
    {
        if ($this->extCommand === 'install') {
            $this->commandInstall();
        } else {
            $this->commandList();
        }

    }

    protected function commandList()
    {
        $t = ConsoleTable::make()
            ->setHeader([
                'Package',
                'Description',
            ]);

        foreach($this->cfg()->packages as $package) {
            $des = $package::description();
            $t->addRow([
                $package::name(),
                ($des ? $des . "\n" : '') . $package::getClassName()
            ]);
        }

        echo $t->render();
    }

    protected function commandInstall()
    {
        $package_name = $this->getFirstBoolArg();

        if (!$package_name) {
            echo Console::textStyle('Params «package_name» is not found', 'black', 'red') . "\n";
            return;
        }

        $package = $this->findPackage($package_name);

        if (!$package) {
            echo Console::textStyle('Package «' . $package_name . '» is not found', 'black', 'red') . "\n";
            return;
        }

        echo Console::textStyle('Install ' . $package::getClassName(), 'black', 'yellow') . "\n";

        /**
         * @var \Spirit\Structure\Package $packageInstance
         */
        $packageInstance = new $package();
        $packageInstance->install();
        echo "\n" . Console::textStyle('Package was installed', 'black', 'green') . "\n";
    }

}