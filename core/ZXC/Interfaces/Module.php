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
    /**
     * Module constructor. Must set only class parameters
     * @param array $config
     */
    public function __construct(array $config = []);

    public function initialize();
}