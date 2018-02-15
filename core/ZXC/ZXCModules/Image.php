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

    /**
     * @param int $width
     * @param int $height
     * @param string $text
     * @param string $fontFile
     * @return array[
     * 'text'=>'given or generated by default text'
     * 'image'=> base64 formatting image
     * ]
     * @throws \Exception
     */
    public function captcha($width = 250, $height = 50, $text = '', $fontFile = '')
    {
        if (!file_exists($fontFile)) {
            throw new \Exception('InvalidArgumentException font file does not exist');
        }
        if (!$text) {
            $text = substr(md5(microtime()), rand(0, 32), 7);
        }

        $charsCount = strlen($text);
        $chars = str_split($text);
        $blockImageWidth = $width / $charsCount;
        $mainImage = imagecreatetruecolor($width, $height);

        $fontSize = $blockImageWidth / 1.5;
        for ($i = 0; $i < $charsCount; $i++) {

            $charBlockImage = imagecreatetruecolor($blockImageWidth, $height);

            $R = mt_rand(0, 255);
            $G = mt_rand(0, 255);
            $B = mt_rand(0, 255);

            $backgroundColor = imagecolorallocate($charBlockImage, 255 - $R, 255 - $G, 255 - $B);
            imagefill($charBlockImage, 0, 0, $backgroundColor);

            $angle = mt_rand(-45, 45);
            $bbox = imagettfbbox($fontSize, $angle, $fontFile, $chars[$i]);
            $fontX = ceil(($blockImageWidth - $bbox[2]) / 2);
            $fontY = ceil(($height - $bbox[7]) / 2);

            $charColor = imagecolorallocate($charBlockImage, $R, $G, $B);
            imagettftext($charBlockImage, $fontSize, $angle, $fontX, $fontY, $charColor, $fontFile, $chars[$i]);

            $src_w = imagesx($charBlockImage);
            $src_h = imagesy($charBlockImage);
            imagecopyresized($mainImage, $charBlockImage, ($i > 0 ? $i * $blockImageWidth : 0), 0, 0, 0, $src_w, $src_h,
                $src_w, $src_h);
            imagepng($charBlockImage);
            imagedestroy($charBlockImage);
        }
        ob_start();
        imagepng($mainImage);
        $resultImage = ob_get_contents();
        ob_end_clean();
        $resultImage = "data:image/png;base64," . base64_encode($resultImage);
        return ['text' => $text, 'image' => $resultImage];
    }

    public function create($width, $height)
    {
        return imagecreatetruecolor($width, $height);

    }
}