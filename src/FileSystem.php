<?php

namespace Spirit;

use Spirit\FileSystem\MimeType;

class FileSystem
{
    public static function get($file_path)
    {
        if (!file_exists($file_path)) {
            return null;
        }

        try {
            return file_get_contents($file_path);
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function put($file_path, $content, $append = false, $info = false)
    {
        $dir = dirname($file_path);

        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        if ($info) {
            $d = debug_backtrace();
            $_file = $d[1]['file'];
            $_line = $d[1]['line'];

            $content = date("Y-m-d H:i:s") . "\n" . $_file . ':' . $_line . "\n" . $content;
        }

        $flag = null;
        if ($append) {
            $content .= "\n";
            $flag = FILE_APPEND;
        }

        try {
            file_put_contents($file_path, $content, $flag);
        } catch (\Exception $e) {
        }
    }

    public static function delete($paths)
    {
        $paths = is_array($paths) ? $paths : func_get_args();

        $success = true;

        foreach($paths as $path) {
            try {
                if (!@unlink($path)) {
                    $success = false;
                }
            } catch (\Exception $e) {
                $success = false;
            }
        }

        return $success;
    }

    public static function move($path, $target)
    {
        return rename($path, $target);
    }

    public static function copy($path, $target)
    {
        $dir = dirname($target);

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        return copy($path, $target);
    }

    public static function name($path)
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }

    public static function basename($path)
    {
        return pathinfo($path, PATHINFO_BASENAME);
    }

    public static function dirname($path)
    {
        return pathinfo($path, PATHINFO_DIRNAME);
    }

    public static function extension($path)
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    public static function type($path)
    {
        return filetype($path);
    }

    public static function mimeType($path)
    {
        return MimeType::get($path);
    }

    public static function size($path)
    {
        return filesize($path);
    }

    public static function lastModified($path)
    {
        return filemtime($path);
    }

    public static function isDirectory($directory)
    {
        return is_dir($directory);
    }

    public static function isWritable($path)
    {
        return is_writable($path);
    }

    public static function isFile($file)
    {
        return is_file($file);
    }

    static function copyDirectory($directory, $destination)
    {
        $dir = opendir($directory);
        mkdir($destination, 0777, true);
        while (false !== ($file = readdir($dir))) {
            if (($file !== '.') && ($file !== '..')) {
                if (is_dir($directory . '/' . $file)) {
                    static::copyDirectory($directory . '/' . $file, $destination . '/' . $file);
                } else {
                    copy($directory . '/' . $file, $destination . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    static function removeDirectory($directory = false, $removeHeadDir = true, $exclude = [])
    {
        if ($directory && is_dir($directory)) {
            $dir = opendir($directory);
            $canRemoveHead = true;
            while (($file = readdir($dir))) {
                if (is_file($directory . '/' . $file)) {
                    if (in_array($file, $exclude)) {
                        $canRemoveHead = false;
                        continue;
                    }

                    unlink($directory . '/' . $file);
                } else if (is_dir($directory . '/' . $file) && ($file != '.') && ($file != '..')) {
                    static::removeDirectory($directory . '/' . $file, true, $exclude);
                }
            }

            closedir($dir);

            if ($removeHeadDir && $canRemoveHead) {
                rmdir($directory);
            }
        }
    }
}