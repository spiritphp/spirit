<?php

namespace Spirit\Common\Controllers;

use Spirit\Engine;
use Spirit\Services\Admin\CommonNavPath as NavPath;
use Spirit\Services\Table;
use Spirit\Request;
use Spirit\FileSystem;
use Spirit\Structure\Controller;

class DebugController extends Controller
{

    use NavPath;

    public function logsAdmin()
    {
        $maindir = Engine::dir()->logs;

        $data = array();

        $nav = [
            [
                'link' => $this->urlA('logs'),
                'title' => 'logs',
                'path' => 'logs'
            ]
        ];

        $dirdir = Request::get('path');

        if ($dirdir) {
            $dirdir = strtr($dirdir, array('../' => '', './' => '', '/..' => '', '..' => ''));
            $dirdir_arr = explode("/", $dirdir);

            $__dopP = '';
            foreach ($dirdir_arr as $item) {
                if (!trim($item)) continue;

                $nav[] = [
                    'link' => $this->urlA('logs?path=' . $__dopP . $item),
                    'title' => $item,
                    'path' => 'logs?path=' . $__dopP . $item
                ];

                $__dopP = $item . '/';
            }

        }

        if (Request::get('remove')) {
            $redirect = $nav[(count($nav) - 2)]['path'];

            if (is_dir($maindir . $dirdir)) {
                FileSystem::removeDirectory($maindir . $dirdir, true);
                return $this->redirectA($redirect);
            } elseif (file_exists($maindir . $dirdir)) {
                unlink($maindir . $dirdir);
                return $this->redirectA($redirect);
            }
        }

        if (is_dir($maindir . $dirdir)) {
            $dirdir .= '/';

            $data['list'] = array();
            foreach (glob($maindir . $dirdir . "*") as $path) {

                $t = str_replace($maindir . $dirdir, '', $path);
                $path = strtr($path, array($maindir => ''));

                if (strpos($path, '/') === 0) {
                    $path = substr($path, 1);
                }

                $data['list'][] = array(
                    'link' => $this->urlA('logs?path=' . $path),
                    'remove_link' => $this->urlA('logs?path=' . $path) . '&remove=1',
                    'remove' => '<i class="glyphicon glyphicon-trash"></i>',
                    'title' => $t
                );

            }

            $data['table'] = Table::make($data['list'])
                ->addColumn('title', ' ')->setLink('{LINK}')
                ->addColumn('remove', ' ')
                ->setWidth(1)
                ->setLink(['{REMOVE_LINK}', ['class' => 'text-danger']]);

        } elseif (file_exists($maindir . $dirdir)) {

            $data['file'] = file_get_contents($maindir . $dirdir);

            $data['file'] = preg_replace("/(\[.+?\]\s([a-z]+)\:.+?)\n/iu", "<span class=\"text-$2\">$1</span>\n", $data['file']);

        } else {
            throw new \Exception('File «' . $maindir . $dirdir . '» does not found');
        }


        $data['nav'] = $this->navpathAdmin($nav);

        return $this->adminview('{__SPIRIT__}/debug/admin/logs.php', $data);
    }
}