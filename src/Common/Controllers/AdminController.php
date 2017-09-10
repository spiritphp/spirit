<?php

namespace Spirit\Common\Controllers;

use Spirit\Structure\Controller;

class AdminController extends Controller
{
    /**
     * Стартовая страница
     */
    public function startAdmin()
    {
        $data = [];

        return $this->adminview('{__SPIRIT__}/admin/admin/start.php', $data);
    }
}