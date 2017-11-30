<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 04.10.2017
 * Time: 16:59
 */

namespace ZXC\Mod;

use ZXC\Interfaces\Module;

class DB implements Module
{
    private $db;
    private $dbType = null;
    private $persistent = null;

    /**
     * DB constructor.
     *
     * @param array $params
     * @throws \Exception
     */
    public function __construct(array $params = []/*, $dsn, $user, $password, $persistent = false*/)
    {
        if (!isset($params['db']) || !isset($params['dns']) ||
            !isset($params['host']) || !isset($params['port']) ||
            !isset($params['name']) || !isset($params['pass'])
        ) {
            throw new \Exception('Error in construct parameters');
        }
        if (!isset($params['persistent'])) {
            $params['persistent'] = false;
        }
        $this->persistent = $params['persistent'];
        try {
            $this->db = new \PDO($params['dns'], $params['name'], $params['pass'],
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
        if (!$this->persistent) {
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