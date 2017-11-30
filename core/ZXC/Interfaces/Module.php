<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 30/11/2017
 * Time: 22:46
 */

namespace ZXC\Interfaces;


interface Module
{
    public function __construct(array $config = []);
}