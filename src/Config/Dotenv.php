<?php

namespace Spirit\Config;

use Spirit\Engine;
use Spirit\Func\Str;
use Spirit\Structure\Single;

class Dotenv extends Single
{

    use Cfg;

    /**
     * @param $k
     * @param null $defaultValue
     * @return mixed|null
     */
    public static function env($k, $defaultValue = null)
    {
        return static::getInstance()->getEnvValue($k, $defaultValue);
    }

    /**
     * @param null $file_path
     * @return static
     */
    public static function make($file_path = null)
    {
        return new static($file_path);
    }

    protected $path;
    protected $options = [];
    protected $original = [];

    public function __construct($file_path = null)
    {
        if ($file_path) {
            $this->path = $file_path;
        } else {
            $this->path = Engine::i()->abs_path . '.env';
        }

        $this->load();
    }

    public function exist()
    {
        return file_exists($this->path);
    }

    protected function load()
    {
        if (!$this->exist()) {
            return;
        }

        $autodetect = ini_get('auto_detect_line_endings');
        ini_set('auto_detect_line_endings', '1');
        $lines = file($this->path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        ini_set('auto_detect_line_endings', $autodetect);

        foreach ($lines as $line) {
            $this->parse($line);
        }
    }

    protected function parse($line)
    {
        list($key, $value) = array_map('trim', explode('=', $line, 2));

        $this->original[$key] = $value;

        if ($key[0] === '#') return;

        $this->options[$key] = $this->value($value);
    }

    protected function value($value)
    {
        if (strtoupper($value) === 'TRUE') {
            $value = true;
        } elseif (strtoupper($value) === 'FALSE') {
            $value = false;
        } elseif (preg_match("/^(\"|')(.+)(\"|')$/",$value,$m)) {
            $value = $m[2];
        } elseif (Str::isJson($value)) {
            $old = $value;
            $value = json_decode($value, 1);

            if (!$value && preg_match("/^\[(.+)\]$/",$old,$m)) {
                $value = array_map('trim', explode(',', $m[1]));
            }
        }

        return $value;
    }

    /**
     * @param $k
     * @param mixed $defaultValue
     * @return mixed|null
     */
    public function getEnvValue($k, $defaultValue = null)
    {
        if (!array_key_exists($k, $this->options)) {
            return $defaultValue;
        }

        return $this->options[$k];
    }

    public function set($k, $v)
    {
        $this->original[$k] = $v;
        $this->options[$k] = $this->value($v);
    }

    public function save($file_path = null)
    {
        $new_original = array_map(function ($key, $value) {
            return $key . '=' . $value;
        }, array_keys($this->original), $this->original);

        file_put_contents(
            $file_path ? $file_path : $this->path,
            implode("\n",$new_original)
        );
    }
}