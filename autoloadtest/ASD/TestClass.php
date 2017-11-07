<?php

namespace ASD;


use ZXC\Factory;
use ZXC\Traits\Helper;

class TestClass extends Factory
{
    use Helper;
    public static $test = ['statvar' => 1];

    public static function qwe( $zxc, $params )
    {
        $z = $zxc;
        $p = $params;
        echo 'qwerqwe';
    }

    public function asd()
    {
        echo 'asd';
    }
}