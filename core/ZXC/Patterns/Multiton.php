<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 09/01/2018
 * Time: 23:01
 */

namespace ZXC\Patterns;


trait Multiton
{
    static private $instance = null;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    private function __wakeup()
    {
    }

    static public function getInstance(string $instanceName)
    {
        return
            !isset(self::$instance[$instanceName])
                ? self::$instance[$instanceName] = new static()//new self()
                : self::$instance[$instanceName];
    }
}