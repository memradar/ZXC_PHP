<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 23/11/2017
 * Time: 23:37
 */
require_once 'Singleton.php';

class Foo2
{
    use Singleton;

    private $bar = 777;

    public function incBar()
    {
        $this->bar++;
    }

    public function getBar()
    {
        return $this->bar;
    }
}