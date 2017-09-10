<?php

namespace Spirit\FileSystem;

class File extends \SplFileInfo
{

    protected $mimeType;

    /**
     * @var bool|null
     */
    protected $isImage;

    /**
     * @var integer|null
     */
    protected $imageType;

    public static function make($path, $checkExist = true)
    {
        return new static($path, $checkExist);
    }

    public function __construct($path, $checkExist = true)
    {
        if ($checkExist && !is_file($path)) {
            throw new \Exception('File «' . $path . '» is not found');
        }

        parent::__construct($path);
    }

    /**
     * @return string
     */
    public function getMimeType()
    {
        if (is_null($this->mimeType)) {
            $this->mimeType = MimeType::get($this->getPathname());
        }

        return $this->mimeType;
    }

    /**
     * @return string|null
     */
    public function guessExtension()
    {
        return MimeType::guessExtension($this->getMimeType());
    }

    /**
     * @param string $to
     * @return static
     * @throws \Exception
     */
    public function move($to)
    {
        if (preg_match("/\/$/", $to)) {
            $to .= $this->getBasename();
        }

        $dir = dirname($to);

        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!@rename($this->getPathname(), $to)) {
            $error = error_get_last();
            throw new \Exception(sprintf('Could not move the file "%s" to "%s" (%s)', $this->getPathname(), $to, strip_tags($error['message'])));
        }

        @chmod($to, 0666 & ~umask());

        return new static($to);
    }

    public function isImage($type = null)
    {
        if (is_null($this->isImage)) {
            $a = getimagesize($this->getPathname());
            $this->imageType = $a[2];

            $this->isImage = false;
            if (in_array($this->imageType, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
                $this->isImage = true;
            }
        }

        if ($this->isImage && $type) {
            return $this->imageType === $type;
        }

        return $this->isImage;
    }
}