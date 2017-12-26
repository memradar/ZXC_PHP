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
        $re2 = $this->db->insert('zxc.users',
            ['login' => 'aaaaaaaaa', 'password' => 'dfasdfasdfasdf', 'email' => 'a@MAIL.RU']);

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
     *          'password'=>''
     *      ]
     * @return mixed
     */
    public function register(array $data)
    {
        // TODO: Implement register() method.
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
}