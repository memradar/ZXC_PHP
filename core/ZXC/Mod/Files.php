<?php

namespace ZXC\Mod;


use ZXC\Factory;
use ZXC\Traits\Helper;

class Files extends Factory
{
    use Helper;
    private $file;
    private $fileName;
    private $fileInfo;
    private $fileParams;
    private $maxFileSize;
    private $uploadDirectory;

    public function __construct($params = [])
    {

    }

    public function isFolder()
    {

    }

    public function move()
    {

    }

    /**
     * @return mixed
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param mixed $fileName
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * @return mixed
     */
    public function getFileParams()
    {
        return $this->fileParams;
    }

    /**
     * @param mixed $fileParams
     */
    public function setFileParams($fileParams)
    {
        $this->fileParams = $fileParams;
    }

    /**
     * @return mixed
     */
    public function getMaxFileSize()
    {
        return $this->maxFileSize;
    }

    /**
     * @param mixed $maxFileSize
     */
    public function setMaxFileSize($maxFileSize)
    {
        $this->maxFileSize = $maxFileSize;
    }

    /**
     * @return mixed
     */
    public function getUploadDirectory()
    {
        return $this->uploadDirectory;
    }

    /**
     * @param mixed $uploadDirectory
     */
    public function setUploadDirectory($uploadDirectory)
    {
        $this->uploadDirectory = $uploadDirectory;
    }
}