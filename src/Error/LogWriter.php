<?php

namespace Spirit\Error;

use Spirit\Engine;
use Spirit\FileSystem;
use Spirit\Services\Mail;

class LogWriter extends LogAbstract {

    protected $isLogExist = false;

    public function write()
    {
        $info = $this->info;

        $f = strtr($this->info->file, [
            Engine::dir()->abs_path => false,
            '/' => '_',
            '\\' => '_',
            ':' => '-'
        ]);

        $log_name = $f . '___' . $this->info->line . '.error';

        $dir = date('Y_m_d') . '/';

        if (!is_dir(Engine::dir()->error . $dir)) {
            mkdir(Engine::dir()->error . $dir, 0755, true);
        }

        $filepath = Engine::dir()->error . $dir . $log_name;

        $this->isLogExist = file_exists($filepath);

        if ($this->isLogExist && !static::cfg()->error['continue']) {
            return;
        } else if (isset(static::cfg()->mail['log']) && count(static::cfg()->mail['log']) > 0) {

            $m = Mail::createMessageRaw($info->message . "\n" . $info->file . "\n" . $info->line)
                ->subject($info->file . ':' . $info->line);

            foreach(static::cfg()->mail['log'] as $mail) {
                $m->to($mail);
            }

            $m->send();
        }

        $str = [];

        $str[] = $this->tableInfo();
        $str[] = $this->tableQuery();

        if (!file_exists($filepath) && $info->trace) {
            $str[] = $this->tableTrace($info->trace);
        }

        $str[] = "\n\n\n";

        FileSystem::put($filepath, implode("\n", $str), true, false);
    }

}