<?php

namespace ZXC\Traits;


trait Helper
{
    //TODO regex for check login email.and password
    public function isAssoc($arr)
    {
        if (array() === $arr) {
            return false;
        }

        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    public function isWindows()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return true;
        } else {
            return false;
        }
    }

    public function isValidLogin($login = '')
    {
        if (!$login) {
            return false;
        }
        return preg_match('/^[A-Za-z][A-Za-z0-9]{3,20}$/', $login);
    }

    public function isValidPassword($sourcePassword)
    {
        //TODO
    }

    public function isStrengthPassword($password)
    {
        //TODO
        return true;
    }

    public function isEmail($email = null, $mx = true)
    {
        if (!$email) {
            return false;
        }
        if (!$mx) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return true;
            }
        }
        return filter_var($email, FILTER_VALIDATE_EMAIL) && (getmxrr(substr($email, strrpos($email, '@') + 1),
                $hosts));

    }

    public function getCleanEmail($email)
    {
        $result = strtolower(filter_var($email, FILTER_SANITIZE_EMAIL));
        return $result;
    }

    public function getPasswordHash($password = null, $cost = 10)
    {
        if ($password === null) {
            throw new \InvalidArgumentException('Password is not defined');
        }
        $options = [
            'cost' => $cost,
        ];
        return password_hash($password, PASSWORD_BCRYPT, $options);
    }

    public function isIP($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP);
    }

    public function equal($val1, $val2)
    {
        return $val1 === $val2;
    }

    public function createHash()
    {
        return md5(time() . rand(0, 150));
    }
}