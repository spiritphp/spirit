<?php

namespace Spirit\Common\Controllers;

use Spirit\Cache;
use Spirit\Engine;
use Spirit\Services\Table;
use Spirit\Request;
use Spirit\FileSystem;
use Spirit\Structure\Controller;

class CleanController extends Controller
{

    protected $defaultConfig = [
        'dir' => [
            'log' => [
                'path' => 'storage/logs/',
                'description' => 'Очищает логи',
            ],
            'sessions' => [
                'path' => 'storage/sessions/',
                'description' => 'Очищает сессии',
            ],
            'cache' => [
                'path' => 'storage/cache/',
                'description' => 'Очищает файловый кэш',
            ]
        ],
        'mcache' => [

        ]
    ];

    /**
     * @param bool $clear_key
     * @return \Spirit\Response
     */
    public function cleanAdmin($clear_key = false)
    {
        $this->title('Очистка папок');
        $data = [];

        $data['success'] = false;

        $cfg = $this->cfg();

        if (Request::get('dir') && $clear_key && isset($cfg['dir'][$clear_key])) {
            $c = &$cfg['dir'][$clear_key];
            $this->removeFiles(Engine::dir()->abs_path . $c['path']);
            $data['success'] = 'Очищена директория <b>' . $c['path'] . '</b>';
        } elseif (Request::get('mcache') && $clear_key && isset($cfg['mcache'][$clear_key])) {
            $this->cleanMcache($clear_key);
            $data['success'] = 'Очищен ключ memcached <b>' . $clear_key . ' (' . $cfg['mcache'][$clear_key] . ')</b>';
        }

        $dir = [];
        foreach ($cfg['dir'] as $k => $v) {
            $dir[] = [
                'key' => $k,
                'path' => Engine::dir()->abs_path . $v['path'],
                'description' => $v['description'],
            ];
        }

        $mcache = [];
        foreach ($cfg['mcache'] as $k => $v) {
            $mcache[] = [
                'key' => $k,
                'description' => $v,
            ];
        }

        $data['table_dir'] = Table::make($dir)
            ->addColumn('key', 'Ключ')
            ->addColumn('path', 'Путь')
            ->addColumn('description', 'Описание')
            ->addColumn('buttonClean', ' ', 'Очистить')->setLink(['/undercover/clean/{KEY}?dir=1', ['class' => 'btn btn-info']]);

        $data['table_mcache'] = Table::make($mcache)
            ->addColumn('key', 'Ключ')
            ->addColumn('description', 'Описание')
            ->addColumn('buttonClean',  ' ', 'Очистить')->setLink(['/undercover/clean/{KEY}?dir=1', ['class' => 'btn btn-info']]);

        return $this->adminview('{__SPIRIT__}/clean/admin/clean.php', $data);
    }

    /**
     * Очистка файлов
     *
     * @param bool $directory
     * @param bool $removeHeadDir
     */
    protected function removeFiles($directory = false, $removeHeadDir = false)
    {
        FileSystem::removeDirectory($directory, $removeHeadDir, ['.gitignore']);
    }

    /**
     * Очистка мемкэша
     *
     * @param $key
     */
    protected function cleanMcache($key)
    {
        Cache::store('memcached')->forget($key);
    }
}