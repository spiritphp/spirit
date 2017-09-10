<?php

namespace Spirit\Config;

use Spirit\Engine;

/**
 * Class Dir
 * @package Spirit\Config
 *
 * @property string $abs_path
 * @property string $spirit_path
 * @property string $config
 * @property string $config_app
 * @property string $config_components
 * @property string $config_services
 * @property string $config_packages
 * @property string $app
 * @property string $path_route
 * @property string $migrations
 * @property string $views
 * @property string $views_component
 * @property string $views_service
 * @property string $spirit_public
 * @property string $spirit_resources
 * @property string $spirit_views
 * @property string $storage
 * @property string $logs
 * @property string $error
 * @property string $cache
 * @property string $sessions
 * @property string $public
 * @property string $images
 */
class Dir {

    protected $data = [];
    protected $is_lock = false;

    /**
     * @var Engine
     */
    protected $engine;

    public function __construct(Engine $engine)
    {
        $this->engine = $engine;
        $this->data = [];
    }

    public function init()
    {
        $this->abs_path = $this->engine->abs_path;
        $this->spirit_path = $this->engine->spirit_path;
        $this->config = $this->abs_path . 'config/';
        $this->config_app = $this->config . 'app/';
        $this->config_components = $this->config. 'components/';
        $this->config_services = $this->config. 'services/';
        $this->config_packages = $this->config. 'packages/';

        $this->app = $this->abs_path . 'app/';
        $this->path_route = $this->app . 'route.php';

        // миграции
        $this->migrations = $this->abs_path . 'migrations/';

        //шаблоны
        $this->views = $this->abs_path . 'views/';
        $this->views_component = $this->views . 'components/';
        $this->views_service = $this->views . 'service/';

        $this->spirit_public = $this->spirit_path . '../public/';
        $this->spirit_resources = $this->spirit_path . '../resources/';
        $this->spirit_views = $this->spirit_resources . 'views/';

        //storage
        $this->storage = $this->abs_path . 'storage/';
        $this->sessions = $this->storage . 'sessions/';
        $this->logs = $this->storage . 'logs/';
        $this->error = $this->logs . 'error/';
        $this->cache = $this->storage . 'cache/';

        $this->public = $this->abs_path . 'public/';
        $this->images = $this->public . 'images/';

        $this->is_lock = true;
    }

    public function __get($name)
    {
        if (!isset($this->data[$name])) {
            throw new \Exception('Not found dir-variable ' . $name);
        }

        return $this->data[$name];
    }

    public function __set($name, $value)
    {
        if ($this->is_lock) return null;

        return $this->data[$name] = $value;
    }

}