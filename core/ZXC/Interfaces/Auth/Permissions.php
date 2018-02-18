<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 16/02/2018
 * Time: 22:49
 */

namespace ZXC\Interfaces\Auth;


interface Permissions
{
    public function setPermissions();

    public function getPermission();
}