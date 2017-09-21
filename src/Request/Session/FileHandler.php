<?php

namespace Spirit\Request\Session;
use Spirit\Engine;

class FileHandler extends \SessionHandler implements \SessionHandlerInterface
{
    private $savePath;

    function open($savePath, $sessionName)
    {
        $this->savePath = Engine::dir()->sessions;
        if (!is_dir($this->savePath)) {
            mkdir($this->savePath, 0777);
        }

        return true;
    }

    function close()
    {
        return true;
    }

    function read($id)
    {
        return is_file($this->savePath . $id) ? file_get_contents($this->savePath . $id) : null;
    }

    function write($id, $data)
    {
        return file_put_contents($this->savePath . $id, $data) === false ? false : true;
    }

    function destroy($id)
    {
        $file = $this->savePath . $id;
        if (file_exists($file)) {
            unlink($file);
        }

        return true;
    }

    function gc($maxlifetime)
    {
        $maxlifetime = 5;
        foreach(glob($this->savePath . "/*") as $file) {
            if (filemtime($file) + $maxlifetime < time() && file_exists($file)) {
                unlink($file);
            }
        }

        return true;
    }
}