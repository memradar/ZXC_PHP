<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 11/01/2018
 * Time: 23:23
 */

namespace ZXC\Classes;


use ZXC\ZXC;

class User
{
    /**
     * @var $db \ZXC\Classes\DB
     */
    private $db;

    public function __construct(array $data = [])
    {
        $this->db = \ZXC\ZXCModules\DB::getInstance();
    }

    /**
     * Register User
     * @throws \Exception
     */

    public function register()
    {
        $data = ZXC::getInstance()->getHttp()->existInput('registerUser');
        if (!$data) {
            throw new \Exception('registerUser not found in HTTP request');
        }
        $data = json_decode($data, true);
        if (!$data) {
            throw new \Exception(' Can not decode request registerUser is not valid JSON');
        }
        if (!$this->checkRegisterInput($data)) {
            throw new \Exception('registerUser data is not valid');
        }

        $login = $data['login'];
        $email = Helper::getCleanEmail($data['email']);
        $joined = date(DATE_RFC822, time());
        $passwordHash = Helper::getPasswordHash($data['password1']);
        $activationKey = Helper::createHash();

        $this->db->insert('zxc.user', [
            'login' => $login,
            'email' => $email,
            'password' => $passwordHash,
            'joined' => $joined,
            'accountactivationkey' => $activationKey
        ]);
    }

    /**
     * Check parameters for register user
     * @param array $data
     * @return bool
     */
    private function checkRegisterInput(array $data = [])
    {
        if (!Helper::equal($data['password1'], $data['password2'])) {
            return false;
        }
        if (!Helper::isStrengthPassword($data['password1'])) {
            return false;
        }
        if (!Helper::isValidLogin($data['login'])) {
            return false;
        }
        if (!Helper::isEmail($data['email'])) {
            return false;
        }
        return true;
    }
}