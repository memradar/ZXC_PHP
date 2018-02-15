<?php

namespace ZXC\ZXCModules;


use ZXC\Classes\FS;
use ZXC\Classes\Helper;

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

    public function captcha($width = 300, $height = 100, $text = '', $fontFile = false)
    {
        $text = Helper::generateRandomText(8, 10);
        $fontFile = ZXC_ROOT . '/../../log/times_new_yorker.ttf';

        $charsCount = strlen($text);
        $chars = str_split($text);
        $blockImageWidth = $width / $charsCount;
        $dst_image = imagecreatetruecolor($width, $height);
        $fontSize = $blockImageWidth / 1.5;
        for ($i = 0; $i < $charsCount; $i++) {

            $src_image = imagecreatetruecolor($blockImageWidth, $height);

            $R = mt_rand(0, 255);
            $G = mt_rand(0, 255);
            $B = mt_rand(0, 255);

            $backgroundColor = imagecolorallocate($src_image, 255 - $R, 255 - $G, 255 - $B);
            imagefill($src_image, 0, 0, $backgroundColor);
            $angle = mt_rand(-45, 45);
            $bbox = imagettfbbox($fontSize, $angle, $fontFile, $chars[$i]);
            $fontX = ceil(($blockImageWidth - $bbox[2]) / 2);
            $fontY = ceil(($height - $bbox[7]) / 2);
            $charColor = imagecolorallocate($src_image, $R, $G, $B);
            imagettftext($src_image, $fontSize, $angle, $fontX, $fontY, $charColor, $fontFile, $chars[$i]);
            $src_w = imagesx($src_image);
            $src_h = imagesy($src_image);
            imagecopyresized(
                $dst_image,
                $src_image,
                ($i > 0 ? $i * $blockImageWidth : 0),
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