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

/**
 * Class DB
 * @package ZXC\Mod
 * @property db PDO
 */
class DB implements Module
{
    /**
     * @var $db \PDO
     */
    private $db;
    private $dns;
    private $name;
    private $pass;
    private $dbType;
    private $persistent;
    private $transaction;

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

    public function exec($query, array $params = [], $fetchStyle = \PDO::FETCH_ASSOC)
    {
        $isArray = is_array($query);
        if ($isArray && count($query) !== count($params)) {
            return false;
        }
        $resultArr = [];
        try {
            if ($isArray) {
                $this->begin();
                foreach ($query as $item => $value) {
                    foreach ($value as $fieldName => $queryValue) {
                        $state = $this->db->prepare($queryValue);
                        $result = $state->execute($value['params']);
                        if ($result) {
                            if (!isset($resultArr[$fieldName])) {
                                $resultArr[$fieldName] = $state->fetchAll($fetchStyle);
                            } else {
                                $resultArr[] = $state->fetchAll($fetchStyle);
                            }
                        }
                        break;
                    }
                }
                $this->commit();
            } else {
                $this->begin();
                $state = $this->db->prepare($query);
                $result = $state->execute($params);
                $this->commit();
                if ($result) {
                    $resultArr[] = $state->fetchAll($fetchStyle);
                }
            }
            return $resultArr;
        } catch (\Exception $e) {
            $this->rollBack();
        }
        return false;
    }

    public function begin()
    {
        $this->db->beginTransaction();
    }

    public function commit()
    {
        $this->db->commit();
    }

    public function rollBack()
    {
        $this->db->rollBack();
    }

    public function error($mode = true)
    {
        if ($mode) {
            return $this->db->errorInfo();
        } else {
            return $this->db->errorCode();
        }
    }
}