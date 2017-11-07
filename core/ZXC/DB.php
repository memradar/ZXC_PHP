<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 04.10.2017
 * Time: 16:59
 */

namespace ZXC;


class DB
{
    private $db /*= null*/
    ;
    private $dbType = null;
    private $persistent = null;

    /**
     * DB constructor.
     *
     * @param      $dsn
     * @param      $user
     * @param      $password
     * @param bool $persistent
     */
    public function __construct($dsn, $user, $password, $persistent = false)
    {
        $this->persistent = $persistent;
        try {
            $this->db     = new \PDO($dsn, $user, $password,
                [\PDO::ATTR_PERSISTENT => $this->persistent]);
            $this->dbType = $this->db->getAttribute(\PDO::ATTR_DRIVER_NAME);
            $this->db->setAttribute(\PDO::ATTR_ERRMODE,
                \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            echo $e;
        }
    }

    /**
     * DB destruct
     */
    public function __destruct()
    {
        if ( ! $this->persistent) {
            $this->db = null;
        }
    }

    /**
     * @return null|\PDO
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * @return mixed|null
     */
    public function getDbType()
    {
        return $this->dbType;
    }
}