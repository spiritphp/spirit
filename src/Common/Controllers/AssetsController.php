<?php

namespace Spirit\Common\Controllers;

use Spirit\Engine;
use Spirit\FileSystem\MimeType;
use Spirit\Structure\Controller;

class AssetsController extends Controller
{
    protected $allowExt = [
        'css','js','png','jpg','jpeg','gif','svg','eot','ttf','woff','woff2'
    ];

    public function read($path)
    {
        if (!preg_match("/\-\-([a-z]{2,5})$/", $path, $m)) {
            $this->abort(404);
        }

        $ext = $m[1];

        $path = str_replace($m[0], '.' . $ext, $path);

        $full_path = Engine::dir()->spirit_public . $path;

        if (!file_exists($full_path)) {
            $this->abort(404);
        }

        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');

        if (!in_array($ext, $this->allowExt, true)) {
            $this->abort(404);
        }

        header("Content-type: " . MimeType::extToType($ext));

        $this->isOnlyThis();

        return file_get_contents($full_path);
    }

}