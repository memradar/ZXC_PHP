<?php

namespace ZXC\Classes;

use ZXC\Patterns\Singleton;

class FS
{
    use Singleton;

    public static function read(string $filePath)
    {
        $result = @file_get_contents($filePath);
        if ($result) {
            return $result;
        }
        return false;
    }

    public static function write(string $filePath, $data, $appendData)
    {
        return file_put_contents($filePath, $data, $appendData ? FILE_APPEND : LOCK_EX);
    }
}