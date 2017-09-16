<?php

namespace Spirit\Undercover;

use Spirit\Console;
use Spirit\Structure\Package;

class UndercoverPackage extends Package {

    protected static $name = 'undercover';
    protected static $description = 'Official admin panel';

    public function install()
    {
        $this->copyAssetsScss(__DIR__ . '/../resources/assets/scss/undercover/', $this->toPackage('undercover/'));
        $this->copyAssetsScss(__DIR__ . '/../resources/assets/scss/undercover.scss',$this->toPackage());
        $this->copyPublicJs(__DIR__ . '/../resources/assets/js/undercover.js',$this->toPackage());
        $this->copyView(__DIR__ . '/../resources/views/layout.php','undercover/');
        $this->copyView(__DIR__ . '/../resources/views/common/',$this->toPackage('common/'));
        $this->copyView(__DIR__ . '/../resources/views/undercover/index.php','undercover/');
        $this->copyRoute(__DIR__ . '/../resources/routes/undercover.php');
        $this->copyConfig(__DIR__ . '/../resources/config/undercover.php');

        $this->copyController(__DIR__ . '/../resources/controllers/UndercoverController.txt','UndercoverController.php');

        echo "\n" . Console::textStyle('Attention','black','light_yellow') . "\n";
        echo 'require \'routes/undercover.php\';' . "\n";
    }
}