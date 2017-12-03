<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 04.10.2017
 * Time: 16:59
 */

namespace ZXC\Mod;

use ZXC\ZXC;
use ZXC\Interfaces\Module;

class DB implements Module
{
    private $db;
    private $dns;
    private $name;
    private $pass;
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
        if (!isset($params['db']) || !isset($params['dbtype']) ||
            !isset($params['host']) || !isset($params['port']) ||
            !isset($params['name']) || !isset($params['pass'])
        ) {
            throw new \Exception('Error in construct parameters');
        }
        if (!isset($params['persistent'])) {
            $params['persistent'] = false;
        }
        $this->dns = isset($params['dns']) ? $params['dns'] : $params['dbtype'] .
            ':host=' . $params['host'] .
            ';dbname=' . $params['db'] .
            ';port=' . $params['port'];

        $this->persistent = $params['persistent'];
        $this->name = $params['name'];
        $this->pass = $params['pass'];
    }

    public function initialize()
    {
        try {
            $this->db = new \PDO($this->dns, $this->name, $this->pass,
                [\PDO::ATTR_PERSISTENT => $this->persistent]);
            $this->dbType = $this->db->getAttribute(\PDO::ATTR_DRIVER_NAME);
            $this->db->setAttribute(\PDO::ATTR_ERRMODE,
                \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            $zxc = ZXC::getInstance();
            $zxc->sysLog($e->getMessage());
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

    public function exec($query, array $params = [])
    {

    }
}