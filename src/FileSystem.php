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

    /**
     * Delete the file at a given path.
     *
     * @param  string|array $paths
     * @return bool
     */
    public static function delete($paths)
    {
        $paths = is_array($paths) ? $paths : func_get_args();

        $success = true;

        foreach ($paths as $path) {
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
        return copy($path, $target);
    }

    /**
     * Extract the file name from a file path.
     *
     * @param  string $path
     * @return string
     */
    public static function name($path)
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }

    /**
     * Extract the trailing name component from a file path.
     *
     * @param  string $path
     * @return string
     */
    public static function basename($path)
    {
        return pathinfo($path, PATHINFO_BASENAME);
    }

    /**
     * Extract the parent directory from a file path.
     *
     * @param  string $path
     * @return string
     */
    public static function dirname($path)
    {
        return pathinfo($path, PATHINFO_DIRNAME);
    }

    /**
     * Extract the file extension from a file path.
     *
     * @param  string $path
     * @return string
     */
    public static function extension($path)
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * Get the file type of a given file.
     *
     * @param  string $path
     * @return string
     */
    public static function type($path)
    {
        return filetype($path);
    }

    /**
     * Get the mime-type of a given file.
     *
     * @param  string $path
     * @return string|false
     */
    public static function mimeType($path)
    {
        return MimeType::get($path);
    }

    /**
     * Get the file size of a given file.
     *
     * @param  string $path
     * @return int
     */
    public static function size($path)
    {
        return filesize($path);
    }

    /**
     * Get the file's last modification time.
     *
     * @param  string $path
     * @return int
     */
    public static function lastModified($path)
    {
        return filemtime($path);
    }

    /**
     * Determine if the given path is a directory.
     *
     * @param  string $directory
     * @return bool
     */
    public static function isDirectory($directory)
    {
        return is_dir($directory);
    }

    /**
     * Determine if the given path is writable.
     *
     * @param  string $path
     * @return bool
     */
    public static function isWritable($path)
    {
        return is_writable($path);
    }

    /**
     * Determine if the given path is a file.
     *
     * @param  string $file
     * @return bool
     */
    public static function isFile($file)
    {
        return is_file($file);
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