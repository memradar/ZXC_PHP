<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 17/11/2017
 * Time: 00:04
 */

namespace ZXC\Mod;


use ZXC\Interfaces\Module;

class Session implements Module
{
    protected $time;
    private $name;
    private $main;
    private $current;

    public function __construct(array $config = [])
    {
        $this->start();
        $_SESSION['id'] = session_id();
        $_SESSION['start'] = $this->getTime();
    }

    public function initialize()
    {
        // TODO: Implement initialize() method.
    }

    public function start()
    {
        if (!isset($_SESSION)) {
            session_start();
        }
    }

    public function end()
    {
        $_SESSION = array();
    }

    public function isEnabled()
    {

    }

    public function getTime()
    {

        $hour = date('H');
        $min = date('i');
        $sec = date('s');
        $month = date('m');
        $day = date('d');
        $year = date('y');

        return mktime($hour, $min, $sec, $month, $day, $year);
    }

    public function __destruct()
    {
        unset($this);
    }
}