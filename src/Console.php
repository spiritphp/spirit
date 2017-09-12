<?php

namespace Spirit;

use Spirit\Console\Commands\AppKey;
use Spirit\Console\Commands\CheckMail;
use Spirit\Console\Commands\Migration;
use Spirit\Console\Commands\Make;
use Spirit\Console\Commands\Help;
use Spirit\Console\Commands\Package;
use Spirit\Response\Session;

class Console
{

    protected static $internal = [
        'migration' => Migration::class,
        'check_mail' => CheckMail::class,
        'package' => Package::class,
        'make' => Make::class,
        'help' => Help::class,
        'app_key' => AppKey::class,
    ];

    public static $aliases = [
        'migrate' => [
            Migration::class,
            'migrate'
        ]
    ];

    protected static $bg = [
        'default' => 49,
        'black' => 40,
        'red' => 41,
        'green' => 42,
        'yellow' => 43,
        'blue' => 44,
        'magenta' => 45,
        'cyan' => 46,
        'light_gray' => 47,
        'dark_gray' => 100,
        'light_red' => 101,
        'light_green' => 102,
        'light_yellow' => 103,
        'light_blue' => 104,
        'light_magenta' => 105,
        'light_cyan' => 106,
        'white' => 107,
    ];

    protected static $color = [
        'default' => 39,
        'black' => 30,
        'red' => 31,
        'green' => 32,
        'yellow' => 33,
        'blue' => 34,
        'magenta' => 35,
        'cyan' => 36,
        'light_gray' => 37,
        'dark_gray' => 90,
        'light_red' => 91,
        'light_green' => 92,
        'light_yellow' => 93,
        'light_blue' => 94,
        'light_magenta' => 95,
        'light_cyan' => 96,
        'white' => 97,
    ];

    protected static $style = [
        'reset' => 0,
        'bold' => 1,
        'dark' => 2,
        'italic' => 3,
        'underline' => 4,
        'blink' => 5,
        'reverse' => 6,
        'concealed' => 7,
    ];

    public static function textStyle($text, $color = 'default', $bg = 'default', $style = false)
    {
        $opt = [];
        if ($color && isset(static::$color[$color])) {
            $opt[] = static::$color[$color];
        }

        if ($bg && isset(static::$bg[$bg])) {
            $opt[] = static::$bg[$bg];
        }

        if ($style && isset(static::$style[$style])) {
            $opt[] = static::$style[$style];
        }

        return "\033[" . implode(';', $opt) . "m" . ($bg === 'default' ? $text : ' ' . $text . ' ') . "\033[0m";
    }

    public static function make($args)
    {
        return new Console($args);
    }

    protected $args = [];
    protected $command;

    public function __construct($args)
    {
        Session::initTest();

        if (isset($args[1])) {
            $this->command = $args[1];
            unset($args[0], $args[1]);

            foreach ($args as $arg) {
                if (preg_match("/^\-\-([a-z0-9_]+)=\"?(.+)?\"?$/iu", $arg, $m)) {
                    $this->args[$m[1]] = $m[2];
                } elseif (preg_match("/^\-([a-z0-9_]+)=\"?(.+)?\"?$/iu", $arg, $m)) {
                    $this->args[$m[1]] = $m[2];
                } else {
                    $this->args[$arg] = true;
                }

            }
        } else {
            $this->command = 'help';
        }

    }

    public static function getAliases()
    {
        return static::$aliases;
    }

    public static function getInternal()
    {
        return static::$internal;
    }

    public function run()
    {
        $ext_command = null;
        if (strpos($this->command, ':') !== false) {
            $commandArr = explode(':', $this->command, 2);
            $this->command = $commandArr[0];
            $ext_command = $commandArr[1];
        }

        $className = '\App\Commands\\' . Func\Str::toCamelCaseClass($this->command);
        $filepath = Engine::i()->abs_path . strtr($className, [
                '\App' => 'app',
                '\\' => '/',
            ]) . '.php';

        if (file_exists($filepath)) {
            // Комманд из \App\Commands
        } elseif (isset(static::$internal[$this->command])) {
            // Внутренняя
            $className = static::$internal[$this->command];
        } elseif (isset(static::$aliases[$this->command])) {
            // Алиас
            if (is_array(static::$aliases[$this->command])) {
                $className = static::$aliases[$this->command][0];
                if (isset(static::$aliases[$this->command][1])) {
                    $ext_command = static::$aliases[$this->command][1];
                }
            } else {
                $className = static::$aliases[$this->command];
            }
        } else {
            echo static::textStyle('Command «' . $this->command . '» is not found ', 'black', 'red');
            echo "\n";
            echo static::textStyle('Class ' . $className . ' is not exist', 'black', 'yellow');
            echo "\n";
            return;
        }

        /**
         * @var \Spirit\Structure\Command $class
         */
        $class = new $className($this->args, $ext_command);

        echo "\n" . static::textStyle('Run class ' . $className, 'black', 'yellow');
        echo "\n" . str_repeat('=', 50) . "\n";
        $class->exec();
        echo "\n";
    }
}