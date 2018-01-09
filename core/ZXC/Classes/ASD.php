<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 09/01/2018
 * Time: 23:03
 */

namespace ZXC\Classes;

class ASD
{
    public $name;
    use \ZXC\Patterns\Multiton;

    public function init($name)
    {
        $this->name = $name;
    }
}