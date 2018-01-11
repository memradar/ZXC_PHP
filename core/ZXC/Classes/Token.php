<?php

namespace ZXC\Classes;

class Token
{
    public static function generate()
    {
        /**
         * @var $session Session
         */
        $session = Session::getInstance();
        return $session->set('zxc_token', md5(uniqid()));
    }

    public static function compare($token = null)
    {
        /**
         * @var $session Session
         */
        $session = Session::getInstance();
        if ($session->get('zxc_token') === $token) {
            $session->delete('zxc_token');
            return true;
        }
        return false;
    }
}