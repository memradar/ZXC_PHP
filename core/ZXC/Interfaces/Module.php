<?php

namespace ZXC\Interfaces;

interface Module
{
    /**
     * Module constructor. Must set only class parameters
     * @param array $config
     */
    public function __construct(array $config = []);

    /**
     * Initialize all needed instances
     * @return mixed
     */
    public function initialize();
}