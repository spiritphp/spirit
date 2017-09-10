<?php

namespace Spirit\View;

/**
 * Class Template
 * @package Spirit\View
 *
 * @method static in($block_name)
 * @method static append($block_name)
 * @method static prepend($block_name)
 * @method static block($block_name)
 * @method static view($tpl, $data = [])
 * @method static save()
 */
class Template
{

    /**
     * @var string|null
     */
    protected static $prepareExtendingFile = null;

    /**
     * @var Layout[]
     */
    protected static $layouts = [];

    /**
     * @var Layout|null
     */
    protected static $extendLayout = null;


    /**
     * @param $path
     * @param null $file
     * @return Layout
     * @throws \Exception
     */
    public static function extend($path, $file = null)
    {
        if (!$file && !static::$prepareExtendingFile) {
            throw new \Exception('Extending file is not found');
        }

        $l = static::layout($path)->extendedBy($file ? $file : static::$prepareExtendingFile);

        static::$prepareExtendingFile = null;

        return $l;
    }

    public static function prepareExtendingFile($file)
    {
        static::$prepareExtendingFile = $file;
    }

    public static function clean(Layout $layout)
    {
        $path = array_search($layout, static::$layouts, true);

        if ($path) {
            unset(static::$layouts[$path]);
        }
    }

    /**
     * @param $path
     * @return Layout
     */
    public static function layout($path)
    {
        if (!isset($layouts[$path])) {
            static::$layouts[$path] = new Layout($path);
        }

        return static::$layouts[$path];
    }

    public static function add(Layout $layout, $key = null)
    {
        static::$layouts[$key] = $layout;
    }

    public static function current()
    {
        if (!count(static::$layouts)) {
            return null;
        }

        /**
         * @var Layout $layout
         */
        $layout = array_values(array_slice(static::$layouts, -1))[0];

        return $layout;
    }

    public static function __callStatic($name, $arguments)
    {
        return static::current()->{$name}(...$arguments);
    }
}