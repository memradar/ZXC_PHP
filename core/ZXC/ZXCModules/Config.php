<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 08/01/2018
 * Time: 22:55
 */

namespace ZXC\ZXCModules;

use ZXC\Patterns\Singleton;

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

    /**
     * @param string $configPath
     * @return bool
     * @throws \Exception
     */
    public static function add(string $configPath = '')
    {
        if (file_exists($configPath)) {
            $config = require_once $configPath;
            if ($config) {
                self::$config = array_merge_recursive(self::$config, $config);
                return true;
            }
            return false;
        } else {
            throw new \Exception(self::get('ZXC/User/codes/Config private file not found'));
        }
    }
}