<?php

namespace ZXC\Interfaces\Auth;

use ZXC\Interfaces\User;

interface Login
{
    public function login(User $user);
}