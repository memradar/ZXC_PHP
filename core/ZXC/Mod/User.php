<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 13/11/2017
 * Time: 00:00
 */

namespace ZXC\Mod;


abstract class User extends Auth
{
    private $email;

    abstract public function load();

    abstract public function save();
}