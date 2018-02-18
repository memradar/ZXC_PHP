<?php

namespace ZXC\Interfaces\Auth;

use ZXC\Interfaces\User;

interface Registration
{
    public function register(User $user);

    public function createConformationLink();
}