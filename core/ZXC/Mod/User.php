<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 23/12/2017
 * Time: 23:10
 */

namespace ZXC\Mod;


use ZXC\Interfaces\UserInterface;
use ZXC\Traits\Helper;
use ZXC\ZXC;

class User implements UserInterface
{
    use Helper;
    private $id;
    /**
     * @var DB
     */
    private $db;
    private $email;
    private $table;
    private $schema;
    private $columns;
    protected $dirMode = 0755;
    /**
     * @var Session
     */
    private $session;
    private $isPageOwner;

    /**
     * UserInterface constructor.
     * @param $data
     */
    public function __construct(array $data = [])
    {
        if (!$data || !isset($data['table']) || !isset($data['schema'])) {
            throw new \InvalidArgumentException();
        }
        $this->table = $data['table'];
        $this->schema = $data['schema'];
        $this->db = ZXC::getInstance()->getModule('DB');
        $this->columns = $this->db->getAllColumns($this->schema, $this->table, \PDO::FETCH_ASSOC);
        $this->session = Session::getInstance();

//        $re = $this->db->select('zxc.users', '*', ['id', '=', 1]);
//        $re2 = $this->db->delete('zxc.users', ['id', '=', 1]);
//        $re2 = $this->db->insert('zxc.users',
//            ['login' => 'aaaaaaaaa', 'password' => 'dfasdfasdfasdf', 'email' => 'a@MAIL.RU']);

        Token::generate();
        Token::compare('fasdfasdf');
    }

    /**
     * Must check User in DB than set Session with User Parameters
     * @param $data array
     *      [
     *          'email'=>'@',
     *          'password'=>'#1er34fsd'
     *          'saveSession'=>true
     *      ]
     * @return mixed
     */
    public function login(array $data)
    {
        if (!$this->isEmail($data['email']) || !$this->isStrengthPassword($data['email']) /*|| !Token::compare($data['token'])*/) {
            return false;
        }
        $fields = array_keys($this->columns);
        $fields = implode(',', $fields);
        $user = $this->db->exec(
            "SELECT {$fields} FROM {$this->schema}.{$this->table} WHERE email = ? AND password = ?",
            [$data['email'], $data['password']]
        );
        if (!$user || count($user) > 1) {
            return false;
        }
        if (isset($data['saveSession']) && $data['saveSession'] === true) {
            //TODO
            $stop = false;
            $this->db = ZXC::getInstance()->getModule('DB');

        }
        $stop = false;
    }

    public function find()
    {

    }

    /**
     * Register User in DB
     * @param $data array
     *      [
     *          'email'=>'',
     *          'login'=>'',
     *          'password1'=>'',
     *          'password2'=>''
     *      ]
     * @return mixed
     */
    public function register(array $data)
    {
        if (!$data ||
            !$this->equal($data['password1'], $data['password2']) ||
            !$this->isValidLogin($data['login']) ||
            !$this->isEmail($data['email'])
        ) {
            throw new \InvalidArgumentException();
        }
        $activationKeyHash = $this->createHash();
        $inserted = $this->db->insert('zxc.users',
            [
                'login' => $data['login'],
                'email' => $data['email'],
                'password' => $this->getPasswordHash($data['password1']),
                'accountactivationkey' => $activationKeyHash,
                'joined' => date(DATE_RFC822, time())
            ]
        );
        if (!$inserted) {
            return false;
        }
        $id = $this->db->getLastInsertId();
        if (!$id) {
            return false;
        }
        if (!$this->createWorkingDir($id)) {
            $zxc = ZXC::getInstance();
            /**
             * @var $logger Logger
             */
            $logger = $zxc->getModule('Logger');
            if (!$logger) {
                return false;
            }
            $logger->critical('!!! ------> User was inserted in DB but can not create working dir',
                ['data' => $data, 'insertedId' => $id]);
        }
        return ['activationKey' => $activationKeyHash, 'uid' => $id];
    }

    /**
     * Remove User Session
     * @return mixed
     */
    public function logout()
    {
        // TODO: Implement logout() method.
    }

    /**
     * Load information about User from DB
     * @param $data array
     *      [
     *          'email'=>'@',
     *          'password'=>'#1er34fsd'
     *      ]
     * @return mixed
     */
    public function load(array $data)
    {
        // TODO: Implement load() method.
    }

    /**
     * @param $data array
     *      [
     *          'table'=>'',
     *          'data'=>[]
     *      ]
     * @return mixed
     */
    public function save(array $data)
    {
        // TODO: Implement save() method.
    }

    /**
     * @param $data array
     *      [
     *          'table'=>'',
     *          'id'=>''
     *      ]
     * @return mixed
     */
    public function delete(array $data)
    {
        // TODO: Implement delete() method.
    }

    /**
     * Check permission for this User
     * @param $permissionName string
     * @return mixed
     */
    public function hasPermission($permissionName)
    {
        // TODO: Implement hasPermission() method.
    }

    /**
     * Return true if this User is page owner
     * @param Session $session
     * @return boolean
     */
    public function isOwner(Session $session)
    {
        // TODO: Implement isOwner() method.
    }

    /**
     * Check User block status in DB
     * @return mixed
     */
    public function isBlocked()
    {
        // TODO: Implement isBlocked() method.
    }

    /**
     * Check User login status
     * @return boolean
     */
    public function isLoggedIn()
    {
        // TODO: Implement isLoggedIn() method.
        return true;
    }

    public function createWorkingDir($userIdFromDB)
    {
        //create root dir for users
        if (!is_dir($_SERVER['DOCUMENT_ROOT'] . '/users/')) {
            if (!@mkdir($_SERVER['DOCUMENT_ROOT'] . '/users/')) {
                return false;
            }
        }
        //create root dir for user
        if (!@mkdir($_SERVER['DOCUMENT_ROOT'] . '/users/user' . $userIdFromDB . '/', $this->dirMode)) {
            return false;
        }
        //dir for user avatars
        if (!@mkdir($_SERVER['DOCUMENT_ROOT'] . '/users/user' . $userIdFromDB . '/avatar', $this->dirMode)) {
            return false;
        }
        //dir for photos
        if (!@mkdir($_SERVER['DOCUMENT_ROOT'] . '/users/user' . $userIdFromDB . '/photo', $this->dirMode)) {
            return false;
        }
        //dir for article photos
        if (!@mkdir($_SERVER['DOCUMENT_ROOT'] . '/users/user' . $userIdFromDB . '/article', $this->dirMode)) {
            return false;
        }
        //dir for tmp files
        if (!@mkdir($_SERVER['DOCUMENT_ROOT'] . '/users/user' . $userIdFromDB . '/tmp', $this->dirMode)) {
            return false;
        }
        return true;
    }
}