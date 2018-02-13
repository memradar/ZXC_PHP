<?php

namespace ZXC\ZXCModules;


use ZXC\Classes\FS;

class Image
{
    protected $imageResource;
    protected $fileName;

    /**
     * Image constructor.
     * @param string $filePath
     * @throws \Exception
     */
    public function __construct(string $filePath)
    {
        if ($filePath) {
            $this->imageResource = $this->load($filePath);
            if (!$this->imageResource) {
                throw new \Exception('Can not read file ' . $filePath);
            }
        }
    }

    public function crop($x1, $y1, $x2, $y2)
    {

    }

    public function load(string $path)
    {
        if (file_exists($path)) {
            $string = FS::read($path);
            if ($string) {
                return imagecreatefromstring($string);
            }
        }
        return false;
    }

    public function save(string $path)
    {

    }

    public function captcha($width = 160, $height = 50, $text = '', $font = false)
    {
//        imagecopyresampled();
        $img = imagecreatetruecolor($width, $height);
        $grey = imagecolorallocate($img, 128, 128, 128);

        imagettftext($img, 15, 0, 11, 21, $grey, ZXC_ROOT . '/../../log/times_new_yorker.ttf', '$text');

        $red   = imagecolorallocate($img, 255,   0,   0);
        imagearc($img, 0, 0, 30, 15, 0, 360, $red);
        imagearc($img, 20, 10, 30, 15, 0, 360, $red);


        $white = imagecolorallocate($img, 0xFF, 0xFF, 0xFF);

        imagedashedline($img, 50, 25, 50, 75, $white);


        imagepng($img, ZXC_ROOT . '/../../log/test.png', 9);
        imagedestroy($img);
    }

    public function create($width, $height)
    {
        return imagecreatetruecolor($width, $height);

    }
}