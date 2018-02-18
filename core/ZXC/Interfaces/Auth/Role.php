<?php

namespace ZXC\Interfaces\Auth;

use ZXC\Interfaces\SQL\Database;

interface Role extends Permissions
{
    public function setDatabase(Database $db);

    public function getRoleFromDatabase($roleName);

    public function checkRole($roleName);
}