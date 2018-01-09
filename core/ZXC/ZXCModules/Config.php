<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 08/01/2018
 * Time: 22:55
 */

namespace ZXC\ZXCModules;

use ZXC\Traits\Singleton;

class Config
{
    use Singleton;
    public static $config;

    public function initialize(array $config = [])
    {
        self::$config = $config;
    }

    public static function get($path)
    {
        if (!$path) {
            return null;
        }
        $path = explode('/', $path);
        $configParameters = self::$config;
        foreach ($path as $item) {
            if (isset($configParameters[$item])) {
                $configParameters = $configParameters[$item];
            } else {
                return null;
            }
        }
        return $configParameters;
    }
}