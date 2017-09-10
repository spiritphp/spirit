<?php

namespace Spirit\Request;

use Spirit\FileSystem\MimeType;

class UploadedFile extends \Spirit\FileSystem\File
{
    protected $error;
    protected $size;
    protected $type;
    protected $originalName;
    protected $mimeType;

    public function __construct(array $file)
    {
        $this->error = $file['error'] ?: UPLOAD_ERR_OK;
        $this->size = $file['size'];
        $this->type = $file['type'] ?: 'application/octet-stream';
        $this->originalName = $file['name'];

        parent::__construct($file['tmp_name'], UPLOAD_ERR_OK === $this->error);
    }

    public function getClientOriginalName()
    {
        return $this->originalName;
    }

    protected function isCheck()
    {
        $isOk = $this->error === UPLOAD_ERR_OK;

        return $isOk && is_uploaded_file($this->getPathname());
    }

    public function getClientOriginalExtension()
    {
        return pathinfo($this->originalName, PATHINFO_EXTENSION);
    }

    public function guessClientExtension()
    {
        return MimeType::guessExtension($this->getClientMimeType());
    }

    public function getClientMimeType()
    {
        return $this->mimeType;
    }

    public function getClientSize()
    {
        return $this->size;
    }

    public function move($to)
    {
        if (!$this->isCheck()) {
            throw new \Exception(UploadedFileError::makeError($this->error, $this->originalName));
        }

        if (preg_match("/\/$/", $to)) {
            $to .= $this->getBasename();
        }

        $dir = dirname($to);

        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!@move_uploaded_file($this->getPathname(), $to)) {
            $error = error_get_last();
            throw new \Exception(sprintf('Could not move the file "%s" to "%s" (%s)', $this->getPathname(), $to, strip_tags($error['message'])));
        }

        @chmod($to, 0666 & ~umask());

        return new \Spirit\FileSystem\File($to);
    }
}