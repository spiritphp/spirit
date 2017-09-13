<?php

namespace Spirit\Undercover;

use Spirit\Structure\Package;

class UndercoverPackage extends Package {

    protected static $name = 'undercover';
    protected static $description = 'Official admin panel';

    public function install()
    {
        $this->copyAssetsScss(__DIR__ . '/../resources/assets/undercover/', 'undercover/');
        $this->copyAssetsScss(__DIR__ . '/../resources/assets/undercover.scss');
        $this->copyPublicJs(__DIR__ . '/../resources/assets/js/undercover.js');
        $this->copyView(__DIR__ . '/../resources/views/layout.php');
        $this->copyView(__DIR__ . '/../resources/views/common/','common/');
    }
}