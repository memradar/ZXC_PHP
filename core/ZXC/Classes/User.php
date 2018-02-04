<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 11/01/2018
 * Time: 23:23
 */

namespace ZXC\Classes;


use PHPUnit\TextUI\Command;
use ZXC\ZXC;
use ZXC\ZXCModules\Config;

class User
{
    /**
     * @var $db \ZXC\Classes\DB
     */
    protected $db;
    /**
     * Keep last error message
     * @var
     */
    private $errorMessage;
    /**
     * Config::get('ZXC/User')
     * @var
     */
    protected $config;
    /**
     * Information about logged in user from user table
     * @var
     */
    private $data;
    /**
     * @var bool
     */
    private $isLoggedIn;
    /**
     * @var bool
     */
    private $isPageOwner;

    /**
     * User constructor.
     * @throws \Exception
     */
    protected function __construct()
    {
        $this->db = \ZXC\ZXCModules\DB::getInstance();
        $this->config = Config::get('ZXC/User');
        if (!$this->config) {
            throw new \Exception('ZXC/User config is not defined in config file');
        }
    }

    /**
     * Register User
     * @return bool | array
     * @throws \Exception
     */

    public function register()
    {
        $data = ZXC::getInstance()->getHttp()->getInput('registerUser');
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
        $joined = date('Y-m-d H:i:s');
        $passwordHash = Helper::getPasswordHash($data['password1']);
        $activationKey = Helper::createHash();

        $insert = $this->db->insert($this->config['table'], [
            $this->config['register']['login'] => $login,
            $this->config['register']['email'] => $email,
            $this->config['register']['password'] => $passwordHash,
            $this->config['register']['joined'] => $joined,
            $this->config['register']['accountactivationkey'] => $activationKey
        ]);
        if (!$insert) {
            $this->errorMessage = $this->db->getErrorMessage();
            $logger = ZXC::getInstance()->getLogger();
            if ($logger) {
                $logger->error($this->errorMessage, $data);
            }
            return false;
        }
        return [
            $this->config['register']['login'] => $login,
            $this->config['register']['email'] => $email,
            $this->config['register']['joined'] => $joined,
            $this->config['register']['accountactivationkey'] => $activationKey
        ];
    }

    /**
     * HTTP::loginUser [password=>'', email=>'']
     * @throws \Exception
     */
    public function login()
    {
        //TODO Device count for login
        $data = ZXC::getInstance()->getHttp()->getInput('loginUser');
        if (!$data) {
            throw new \Exception('loginUser not found in HTTP request');
        }
        $data = json_decode($data, true);
        if (!$data) {
            throw new \Exception(' Can not decode request loginUser is not valid JSON');
        }
        if (!$this->checkLoginInput($data)) {
            throw new \Exception('loginUser data is not valid');
        }
        $user = $this->find($data['email']);
        if (!$user || $user['block'] === 1) {
            $this->errorMessage = Config::get('ZXC/User/codes/User is blocked');
            return false;
        }
        if (!Helper::passwordVerify($data['password'], $user['password'])) {
            $this->errorMessage = Config::get('ZXC/User/codes/Invalid password');
            return false;
        }
        $this->data = $user;
        $this->isLoggedIn = true;

        $remember = isset($data['remember']) && $data['remember'] === true ? true : false;
        if ($remember) {
            $userHashFromDB = $this->db->select($this->config['table_session'], '*',
                ['userid', '=', $this->data['id']]);
            if (!$userHashFromDB) {
                $hash = Helper::createHash();
                $insertData = ['userid' => $this->data['id'], 'session' => $hash];
                $insertResult = $this->db->insert($this->config['table_session'], $insertData);
                if (!$insertResult) {
                    $this->errorMessage = Config::get('ZXC/User/codes/Error insert data in session table') . ' ' . $this->config['table_session'] . 'see log file';
                    $logger = ZXC::getInstance()->getLogger();
                    if ($logger && $logger->getLevel() === 'debug') {
                        $logger->error('Can not insert data in table ' . $this->config['table_session'], $insertData);
                    }
                }
            } else {
                $hash = $userHashFromDB[0]['session'];
            }
            Cookie::set($this->config['remember']['name'], $hash, $this->config['remember']['expiry']);
        }

        $session = Session::getInstance();
        $session->set('User', [
            'id' => $user['id'],
            'login' => $user['login'],
            'email' => $user['email'],
            'fname' => $user['firstname'],
            'lname' => $user['lastname']
        ]);
        return true;
    }

    public function logout()
    {
        $session = Session::getInstance();
        $session->delete('User');
        Cookie::delete($this->config['remember']['name']);
        return true;
    }

    public function deleteAllOtherSessions()
    {
        $hash = Helper::createHash();
        Cookie::delete($this->config['remember']['name']);
        $insertData = ['userid' => $this->data['id'], 'session' => $hash];
        $insertResult = $this->db->insert($this->config['table_session'], $insertData);
        if (!$insertResult) {
            $this->errorMessage = 'Error insert data in table ' . $this->config['table_session'] . ' function deleteAllOtherSessions see log file';
            $logger = ZXC::getInstance()->getLogger();
            if ($logger && $logger->getLevel() === 'debug') {
                $logger->error('Can not insert data in table ' . $this->config['table_session'], $insertData);
            }
        }
        Cookie::set($this->config['remember']['name'], $hash, $this->config['remember']['expiry']);
    }

    /**
     * Get field name fo search user from string
     * @param $string
     * @return string
     */
    public function getFieldFromString($string)
    {
        if (is_string($string)) {
            if (Helper::isEmail($string)) {
                return 'email';
            } else {
                return 'login';
            }
        } else {
            return 'id';
        }
    }

    /**
     * Find user by id | login | email
     * @param null $userEmailOrIdOrLogin
     * @return bool
     */
    public function find($userEmailOrIdOrLogin = null)
    {
        $field = $this->getFieldFromString($userEmailOrIdOrLogin);
        $result = $this->db->select($this->config['table'], '*', [$field, '=', $userEmailOrIdOrLogin]);
        if (!$result) {
            return false;
        }
        return $result[0];
    }

    private function checkLoginInput(array $data = [])
    {
        if (!isset($data['email']) || !isset($data['password'])) {
            return false;
        }
        if (!Helper::isEmail($data['email'])) {
            return false;
        }
        if (!Helper::isStrengthPassword($data['password'])) {
            return false;
        }
        return true;
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

    /**
     * @return mixed
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @param mixed $errorMessage
     */
    public function setErrorMessage($errorMessage): void
    {
        $this->errorMessage = $errorMessage;
    }

    /**
     * @param null $userEmailOrId
     * @return bool
     * @throws \Exception
     */
    public function fetch($userEmailOrId = null)
    {
        if (!$userEmailOrId) {
            throw new \Exception('$userEmailOrId is not defined');
        }
        $this->data = $this->find($userEmailOrId);
        if (!$this->data) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param string $key
     * @return bool|array
     * @throws \Exception
     */
    public function checkHTTP(string $key)
    {
        $data = ZXC::getInstance()->getHttp()->getInput($key);
        if (!$data) {
            throw new \Exception('confirmEmail not found in HTTP request');
        }
        $data = json_decode($data, true);
        if (!$data) {
            throw new \Exception(' Can not decode request confirmEmail is not valid JSON');
        }
        return $data;
    }

    /**
     * @throws \Exception
     */
    public function confirmEmail()
    {
        $data = $this->checkHTTP('confirmEmail');
        if (!$this->checkConfirmEmailInput($data)) {
            throw new \Exception('loginUser data is not valid');
        }
        $user = $this->find($data['login']);
        if (!$user) {
            $this->errorMessage = Config::get('ZXC/User/codes/Can not confirm email for user');
            return false;
        }
        if ($user['block'] !== 1) {
            $this->errorMessage = Config::get('ZXC/User/codes/Can not confirm email for user, user has confirmed email');
            return false;
        }
        if ($user[$this->config['confirmation']['key']] !== $data['key']) {
            $this->errorMessage = Config::get('ZXC/User/codes/Can not confirm email for user invalid key');
            return false;
        }
        $result = $this->db->update($this->config['table'], [$this->config['confirmation']['key'] => '', 'block' => 0],
            [$this->config['login']['login'], '=', $data['login']]);
        if (!$result) {
            $this->errorMessage = Config::get('ZXC/User/codes/Can not update values in db for email conformation');
            return false;
        }
        return true;
    }

    public function checkConfirmEmailInput($data)
    {
        if (isset($data['login']) && isset($data['key'])) {
            return true;
        }
        return false;
    }
}