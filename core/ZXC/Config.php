<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 29/11/2017
 * Time: 22:19
 */

namespace ZXC;


class Config extends Factory
{

    public function __construct(array $config = [], $src = null)
    {
        $this->initialize($config, $src);
    }

    public function initialize(array $config)
    {
        // TODO: Implement initialize() method.
    }
}