<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 13/11/2017
 * Time: 00:00
 */

namespace ZXC\Interfaces;


use ZXC\Mod\Session;

interface UserInterface
{
    /**
     * UserInterface constructor.
     * @param $data
     *      [
     *          'schema'=>'zxc'
     *          'table'=>'users'
     *      ]
     */
    public function __construct(array $data);

    /**
     * Must check User in DB than set Session with User Parameters
     * @param $data array
     *      [
     *          'email'=>'@',
     *          'password'=>'#1er34fsd'
     *      ]
     * @return mixed
     */
    public function login(array $data);

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
    public function register(array $data);

    /**
     * Remove User Session
     * @return mixed
     */
    public function logout();

    /**
     * Load information about User from DB
     * @param $data array
     *      [
     *          'email'=>'@',
     *          'password'=>'#1er34fsd'
     *      ]
     * @return mixed
     */
    public function load(array $data);

    /**
     * @param $data array
     *      [
     *          'table'=>'',
     *          'data'=>[]
     *      ]
     * @return mixed
     */
    public function save(array $data);

    /**
     * @param $data array
     *      [
     *          'table'=>'',
     *          'id'=>''
     *      ]
     * @return mixed
     */
    public function delete(array $data);

    /**
     * Check permission for this User
     * @param $permissionName string
     * @return mixed
     */
    public function hasPermission($permissionName);

    /**
     * Return true if this User is page owner
     * @param Session $session
     * @return boolean
     */
    public function isOwner(Session $session);

    /**
     * Check User block status in DB
     * @return mixed
     */
    public function isBlocked();

    /**
     * Check User login status
     * @return boolean
     */
    public function isLoggedIn();

    /**
     * @param $userIdFromDB
     * @return mixed
     */
    public function createWorkingDir($userIdFromDB);
}