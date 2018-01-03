<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 03/01/2018
 * Time: 17:06
 */

namespace ZXC\Mod\Auth;


class Role
{
    protected $permissions;

    protected function __construct()
    {
        $this->permissions = [];
    }

}