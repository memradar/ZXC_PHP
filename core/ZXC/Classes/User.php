<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 11/01/2018
 * Time: 23:23
 */

namespace ZXC\Classes;


use ZXC\Classes\Mail\Mailer;
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
    private $isLoggedIn = false;
    /**
     * @var bool
     */
    private $isPageOwner;
    /**
     * @var string
     */
    private $smtpServer;
    /**
     * @var int
     */
    private $smtpPort;
    /**
     * @var bool
     */
    private $smtpSSL;
    /**
     * @var string
     */
    private $smtpUser;
    /**
     * @var string
     */
    private $smtpPassword;
    /**
     * @var string
     */
    private $smtpFrom;
    /**
     * @var string
     */
    private $smtpFromEmail;
    /**
     * @var bool
     */
    private $isSMTPActive = false;
    /**
     * @var Mailer
     */
    private $mailer;
    /**
     * @var bool
     */
    protected $confirmationEnabled = false;

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
        $this->confirmationEnabled = Config::get('ZXC/User/confirmation/enabled');
        $session = Session::getInstance();
        $sessionUser = $session->get('User');
        $findUserBy = false;
        if ($sessionUser) {
            $findUserBy = $sessionUser['login'];
        } else {
            $hasHash = Cookie::get($this->config['remember']['name']);
            if ($hasHash) {
                $findUserBy = $this->getUserIdBySessionHash($hasHash);
            }
        }
        if ($findUserBy) {
            if ($this->fetch($findUserBy)) {
                $this->isLoggedIn = true;
                $this->setUserSession();
            }
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

        if ($this->confirmationEnabled) {
            $insertData = [
                $this->config['register']['login'] => $login,
                $this->config['register']['email'] => $email,
                $this->config['register']['password'] => $passwordHash,
                $this->config['register']['joined'] => $joined,
                $this->config['register']['accountactivationkey'] => $activationKey
            ];
        } else {
            $insertData = [
                $this->config['register']['login'] => $login,
                $this->config['register']['email'] => $email,
                $this->config['register']['password'] => $passwordHash,
                $this->config['register']['joined'] => $joined,
                $this->config['register']['accountactivationkey'] => '',
                $this->config['register']['block'] => 0,
                $this->config['register']['accountactivationattr'] => 1
            ];
        }

        $insert = $this->db->insert($this->config['table'], $insertData);
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
        if ($this->isLoggedIn) {
            return true;
        }
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
            $hash = Helper::createHash();
            if ($this->data['device_count'] === 1) {
                $this->db->delete($this->config['table_session'], ['userid', '=', $this->data['id']]);
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
                $userHashFromDB = $this->db->select($this->config['table_session'], '*',
                    ['userid', '=', $this->data['id']]);
                if (!$userHashFromDB) {
                    $insertData = ['userid' => $this->data['id'], 'session' => $hash];
                    $insertResult = $this->db->insert($this->config['table_session'], $insertData);
                    if (!$insertResult) {
                        $this->errorMessage = Config::get('ZXC/User/codes/Error insert data in session table') . ' ' . $this->config['table_session'] . 'see log file';
                        $logger = ZXC::getInstance()->getLogger();
                        if ($logger && $logger->getLevel() === 'debug') {
                            $logger->error('Can not insert data in table ' . $this->config['table_session'],
                                $insertData);
                        }
                    }
                } else {
                    $hash = $userHashFromDB[0]['session'];
                }
            }
            if (!Cookie::set($this->config['remember']['name'], $hash, $this->config['remember']['expiry'])) {
                $logger = ZXC::getInstance()->getLogger();
                if ($logger && $logger->getLevel() === 'debug') {
                    $logger->warning('Can not se cookie');
                }
            }
        }
        $this->setUserSession();
        return true;
    }

    private function setUserSession()
    {
        if (!$this->data) {
            return false;
        }
        $session = Session::getInstance();
        $session->set('User', [
            'id' => $this->data['id'],
            'login' => $this->data['login'],
            'email' => $this->data['email'],
            'fname' => $this->data['firstname'],
            'lname' => $this->data['lastname']
        ]);
        return true;
    }

    public function logout()
    {
        $session = Session::getInstance();
        $session->delete('User');
        Cookie::delete($this->config['remember']['name']);
        $this->isLoggedIn = false;
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

    /**
     * @param string $hash
     * @return bool
     */
    public function getUserIdBySessionHash($hash = '')
    {
        if (!$hash) {
            return false;
        }
        $result = $this->db->select($this->config['table_session'], '*', ['session', '=', $hash]);
        if (!$result) {
            return false;
        }
        return $result[0]['userid'];
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
     * @param null $userEmailOrIdOrLogin
     * @return bool
     * @throws \Exception
     */
    public function fetch($userEmailOrIdOrLogin = null)
    {
        //TODO
        if (!$userEmailOrIdOrLogin) {
            throw new \Exception('$userEmailOrId is not defined');
        }
        $this->data = $this->find($userEmailOrIdOrLogin);
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
        if ($user['block'] !== 1 || $user[$this->config['register']['accountactivationattr']] !== 0) {
            $this->errorMessage = Config::get('ZXC/User/codes/Can not confirm email for user, user has confirmed email');
            return false;
        }
        if ($user[$this->config['register']['accountactivationkey']] !== $data['key']) {
            $this->errorMessage = Config::get('ZXC/User/codes/Can not confirm email for user invalid key');
            return false;
        }
        $result = $this->db->update($this->config['table'],
            [
                $this->config['register']['accountactivationkey'] => '',
                $this->config['register']['block'] => 0,
                $this->config['register']['accountactivationattr'] => 1
            ],
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

    /**
     * @return bool
     */
    public function isLoggedIn(): bool
    {
        return $this->isLoggedIn;
    }

    /**
     * @param array $config
     * @return bool
     * @throws \Exception
     */
    protected function setMailAuthProperties(array $config = [])
    {
        if (!$config || !isset($config['server'])
            || !isset($config['port']) || !isset($config['ssl'])
            || !isset($config['user']) || !isset($config['password'])
            || !isset($config['from']) || !isset($config['fromEmail'])) {
            $this->isSMTPActive = false;
            throw new \Exception(Config::get('ZXC/User/codes/Config for connect not defined'));
        }
        $this->smtpSSL = $config['ssl'];
        $this->smtpUser = $config['user'];
        $this->smtpFrom = $config['from'];
        $this->smtpPort = $config['port'];
        $this->smtpServer = $config['server'];
        $this->smtpPassword = $config['password'];
        $this->smtpFromEmail = $config['fromEmail'];
        $this->isSMTPActive = true;
        $this->mailer = new Mailer();
        $this->mailer->setServer($this->smtpServer, $this->smtpPort, $this->smtpSSL)
            ->setAuth($this->smtpUser, $this->smtpPassword)
            ->setFrom($this->smtpFrom, $this->smtpFromEmail);
        return true;
    }

    /**
     * @return bool
     */
    public function isIsSMTPActive(): bool
    {
        return $this->isSMTPActive;
    }

    /**
     * @param string $body
     * @param string $subject
     * @param string $userName
     * @param string $userEmail
     * @return bool
     * @throws \Exception
     */
    protected function sendEmail(string $body, string $subject, string $userName, string $userEmail): bool
    {
        $result = $this->mailer->addTo($userName, $userEmail)->setSubject($subject)->setBody($body)->send();
        return $result;
    }

    public function getLogin(){
        if(isset($this->data['login'])){
            return $this->data['login'];
        }
        return false;
    }
}