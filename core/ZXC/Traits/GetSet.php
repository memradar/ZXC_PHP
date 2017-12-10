<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 04/12/2017
 * Time: 23:59
 */

namespace ZXC\Traits;


trait GetSet
{
    private $ignoreList = [];

    public function get($key)
    {
        if (isset($this->$key) && !isset($this->ignoreList[$key])) {
            return $this->$key;
        } else {
            return false;
        }

    }

    public function set($key, $value)
    {
        if (!isset($this->ignoreList[$key])) {
            return $this->$key = $value;
        } else {
            return false;
        }
    }
}