<?php

namespace ZXC\Interfaces;

use ZXC\Interfaces\Auth\Authentication;

interface User extends \JsonSerializable, Authentication
{
    public function getFirstName();

    public function getLastName();

    public function getId();

    public function getEmail();

    public function jsonSerialize();
}