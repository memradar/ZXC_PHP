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
    public function __construct(string $filePath = '')
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

    public function captcha($width = 200, $height = 100, $text = '', $font = false)
    {

        $count = $width / 5;
        $dst_image = imagecreatetruecolor($width, $height);
        for ($i = 0; $i < 7; $i++) {
            $src_image = imagecreatetruecolor($count, $height);
            $color = imagecolorallocate($src_image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            imagettftext($src_image, 25, 0, 20, 50, $color, ZXC_ROOT . '/../../log/times_new_yorker.ttf', "$i");
            $src_w = imagesx($src_image);
            $src_h = imagesy($src_image);
            imagecopyresized(
                $dst_image,
                $src_image,
                ($i > 0 ? $i * $count : 0),
                0,
                0,
                0,
                $src_w,
                $src_h,
                $src_w,
                $src_h
            );
            imagepng($src_image, ZXC_ROOT . '/../../log/test' . $i . '.png', 9);
            imagedestroy($src_image);
        }
        //
        imagepng($dst_image, ZXC_ROOT . '/../../log/test.png', 9);
    }

    public function create($width, $height)
    {
        return imagecreatetruecolor($width, $height);

    }
}