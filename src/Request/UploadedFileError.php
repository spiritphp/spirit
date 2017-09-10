<?php

namespace Spirit\Request;

class UploadedFileError {

    static $errors = [
        UPLOAD_ERR_INI_SIZE => 'The file "%s" exceeds your upload_max_filesize ini directive (limit is %d KiB).',
        UPLOAD_ERR_FORM_SIZE => 'The file "%s" exceeds the upload limit defined in your form.',
        UPLOAD_ERR_PARTIAL => 'The file "%s" was only partially uploaded.',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
        UPLOAD_ERR_CANT_WRITE => 'The file "%s" could not be written on disk.',
        UPLOAD_ERR_NO_TMP_DIR => 'File could not be uploaded: missing temporary directory.',
        UPLOAD_ERR_EXTENSION => 'File upload was stopped by a PHP extension.',
    ];

    public static function getMaxFilesize()
    {
        $iniMax = strtolower(ini_get('upload_max_filesize'));

        if ('' === $iniMax) {
            return PHP_INT_MAX;
        }

        $max = ltrim($iniMax, '+');
        if (0 === strpos($max, '0x')) {
            $max = intval($max, 16);
        } elseif (0 === strpos($max, '0')) {
            $max = intval($max, 8);
        } else {
            $max = (int)$max;
        }

        switch (substr($iniMax, -1)) {
            case 't':
                $max *= 1024;
                break;
            case 'g':
                $max *= 1024;
                break;
            case 'm':
                $max *= 1024;
                break;
            case 'k':
                $max *= 1024;
        }

        return $max;
    }

    public static function makeError($code, $name)
    {
        $maxFilesize = $code === UPLOAD_ERR_INI_SIZE ? self::getMaxFilesize() / 1024 : 0;
        $message = isset(static::$errors[$code]) ? static::$errors[$code] : 'The file "%s" was not uploaded due to an unknown error.';

        return sprintf($message, $name, $maxFilesize);
    }

}