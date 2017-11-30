<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 23/11/2017
 * Time: 23:44
 */

namespace ZXC;


trait Singleton
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

    static public function getInstance()
    {
        return
            self::$instance === null
                ? self::$instance = new static()//new self()
                : self::$instance;
    }
}