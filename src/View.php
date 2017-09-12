<?php

namespace Spirit;

use Spirit\View\Template;

class View
{

    /**
     * @var View[]
     */
    static protected $views = [];
    protected $file;
    protected $data;
    protected $timeStart;
    protected $timeEnd;
    protected $log = [
        'path' => null,
        'params' => [],
        'time' => null,
        'memory1' => null,
        'memory2' => null,
    ];

    public function __construct($file, $data = [])
    {
        if (strpos($file, '::') !== false) {
            list($package, $file) = explode('::',$file,2);

            $file = 'packages/' . $package . '/' . $file;
        }

        if (mb_substr($file, 0, 1, "UTF-8") !== '/') {
            $file = Engine::dir()->views . $file;
        }

        if (!pathinfo($file, PATHINFO_EXTENSION)) {
            $file .= '.php';
        }

        if (!is_array($data)) {
            $data = [$data];
        }

        $this->file = $file;
        $this->data = $data;

        static::$views[] = $this;
    }

    /**
     * @param $file
     * @param array $data
     * @return static
     */
    public static function make($file, $data = [])
    {
        return new static($file, $data);
    }

    public static function logs()
    {
        $logs = [];
        foreach(static::$views as $v) {
            $logs[] = $v->getLog();
        }

        return $logs;
    }

    public function getLog()
    {
        return [
            'path' => $this->file,
            'params' => array_keys($this->data),
            'time' => $this->log['time'],
            'memory' => $this->log['memory2'] . ' (' . $this->log['memory1'] . ')',
        ];
    }

    public function render()
    {
        $this->logStart();

        Template::prepareExtendingFile($this->file);

        ob_start();
        Engine::i()->includeFile($this->file, $this->data);
        $render = ob_get_contents();
        ob_end_clean();

        $template = Template::current();
        if ($template && $template->isExtendingView($this->file)) {
            $render = $template->render();
            Template::clean($template);
        }

        $this->logEnd();

        return $render;
    }

    public function __toString()
    {
        return $this->render();
    }

    protected function logStart()
    {
        $this->timeStart = microtime(true);
    }

    protected function logEnd()
    {
        $this->timeEnd = microtime(true);

        if (!isDebug()) {
            return;
        }

        $this->log = [
            'time' => ($this->timeEnd - $this->timeStart),
            'memory1' => memory_get_peak_usage(),
            'memory2' => memory_get_usage()
        ];
    }

}