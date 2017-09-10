<?php

namespace Spirit\Auth;

class AppDriver extends DefaultDriver
{
    protected $app;

    /**
     * @var AppDriver\Platform
     */
    protected static $driver;

    /**
     * @var array
     */
    protected static $config;

    public static function driver($driver, $config)
    {
        static::$config = $config;
        static::$driver = $driver;
    }

    protected function attemptAuth()
    {
        $this->attemptAppAuth();

        parent::attemptAuth();
    }

    protected function attemptAppAuth()
    {
        $this->app = $this->initApp();
        $user = $this->app->appUser();

        if (!$user) return;

        $this->setUserCookie($user->id);
    }

    protected function initApp()
    {
        if (!static::$config) {
            throw new \Exception('Empty config');
        }

        if (!static::$driver) {
            throw new \Exception('Empty driver');
        }

        /**
         * @var AppDriver\Platform $class
         */
        return new static::$driver(static::$config);
    }
}