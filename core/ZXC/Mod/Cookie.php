<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 08/01/2018
 * Time: 18:33
 */

namespace ZXC\Mod;


class Cookie
{
    public static function exists($name)
    {
        return isset($_COOKIE[$name]) ? true : false;
    }

    public static function get($name)
    {
        return $_COOKIE[$name];
    }

    public static function put($name, $value, $expire)
    {
        if (!setcookie($name, $value, time() + $expire, '/')) {
            return true;
        }
        return false;
    }

    public static function delete($name)
    {
        self::put($name, '', time() - 1);
    }
}