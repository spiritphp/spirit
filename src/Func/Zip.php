<?php

namespace Spirit\Func;

use ZipArchive;

class Zip
{

    public static function folder($folder, $zip_file)
    {
        $dir = dirname($zip_file);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $rootPath = realpath($folder);

        $zip = new ZipArchive();
        $zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        /**
         * @var \SplFileInfo[] $files
         */
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($rootPath), \RecursiveIteratorIterator::LEAVES_ONLY);

        foreach ($files as $name => $file) {

            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();
    }

    public static function extractToFolder($zip_file, $folder)
    {
        if (!is_dir($folder)) {
            mkdir($folder, 0755, true);
        }

        $zip = new ZipArchive;

        if ($zip->open($zip_file)) {
            $zip->extractTo($folder);
            $zip->close();
        }
    }
}