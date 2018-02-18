<?php

namespace ZXC\Interfaces\Auth;

interface Authentication extends Role
{
    public function getPassword();

    public function setAuthFields();

    public function getUserSessionToken();

    public function setUserSessionToken();

}