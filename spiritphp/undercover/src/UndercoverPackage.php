<?php

namespace Spirit\Undercover;

use Spirit\Structure\Package;

class UndercoverPackage extends Package {

    protected static $name = 'undercover';
    protected static $description = 'Official admin panel';

    public function install()
    {
        $this->copyAssetsScss(__DIR__ . '/../resources/assets/undercover.scss');
        $this->copyAssetsScss(__DIR__ . '/../resources/assets/undercover/', 'undercover/');
    }
}