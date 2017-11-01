<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 27/10/2017
 * Time: 23:07
 */

namespace ASD;


use ZXC\Factory;

class TestClass extends Factory
{
    public static $test = [ 'statvar' => 1 ];

    public static function qwe( $zxc, $params )
    {
        $z = $zxc;
        $p = $params;
        echo self::$test['statvar'];
    }

    public function asd()
    {
        echo 'asd';
    }

    public function qpost()
    {
        echo 'main post route';
    }

    public function qget()
    {
        echo 'main get route';
    }
}