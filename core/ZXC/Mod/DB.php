<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 04.10.2017
 * Time: 16:59
 */

namespace ZXC\Mod;

use ZXC\Factory;
use ZXC\Traits\Singleton;
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
    private $error;
    private $dbType;
    private $columnsBlocked;
    private $persistent;
    private $transaction;
    private $conditions = ['<', '>', '<=', '>=', '='];
    private $lastResult;
    private $lastInsertId;

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
        $this->columnsBlocked = isset($params['columns']) ? $params['columns'] : null;
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
//            $zxc->sysLog($e->getMessage());
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
        $isInsert = stripos($query, 'insert') === 0;
        $isArray = is_array($query);
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
                    $resultArr = $state->fetchAll($fetchStyle);
                }
            }
            $this->lastResult = $resultArr;
            if ($isInsert) {
                $this->lastInsertId = $this->db->lastInsertId();
            }
            return $resultArr;
        } catch (\Exception $e) {
            $this->rollBack();
            $this->error = true;
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

    public function getAllColumns($tableSchema, $tableName, $fetchStyle = \PDO::FETCH_ASSOC)
    {
        $columns = [];
        $query = 'SELECT * FROM information_schema.columns WHERE table_schema = ? AND table_name = ?';
        $result = $this->exec($query, [$tableSchema, $tableName], $fetchStyle);
        if (!$result) {
            return $columns;
        }
        $tableInConfig = isset($this->columnsBlocked[$tableName]) && isset($this->columnsBlocked[$tableName]['blocked'])
            ? $this->columnsBlocked[$tableName]['blocked'] : null;
        $isObject = is_object($result[0]);
        foreach ($result as $item) {
            if (is_object($item)) {

                if (isset($tableInConfig[$item->column_name]) && !$tableInConfig[$item->column_name]) {
                    $columns[$item->column_name] = null;
                } elseif (!isset($tableInConfig[$item->column_name])) {
                    $columns[$item->column_name] = null;
                }
            } else {
                if (isset($tableInConfig[$item['column_name']]) && !$tableInConfig[$item['column_name']]) {
                    $columns[$item['column_name']] = null;
                } elseif (!isset($tableInConfig[$item['column_name']])) {
                    $columns[$item['column_name']] = null;
                }
            }
        }
        return $isObject ? (object)$columns : $columns;
    }

    private function execAction($object = [])
    {
        if (!$object || !isset($object['action']) || !isset($object['table']) || !isset($object['where'])
            || !is_array($object['where']) || count($object['where']) !== 3) {
            throw new \InvalidArgumentException('Argument is not correct');
        }

        if (in_array($object['where'][1], $this->conditions)) {
            $query = "{$object['action']} {$object['fields']} FROM {$object['table']} WHERE  {$object['where'][0]} = ?";
            return $this->exec($query, [$object['where'][2]]);
        }
        return false;
    }

    public function select($table, $fields = '*', $where = [])
    {
        if (!$table || !$where) {
            throw new \InvalidArgumentException();
        }
        return $this->execAction([
            'action' => 'select',
            'table' => $table,
            'fields' => $fields,
            'where' => $where
        ]);
    }

    public function delete($table, $where = [])
    {
        if (!$table || !$where) {
            throw new \InvalidArgumentException();
        }
        return $this->execAction([
            'action' => 'delete',
            'table' => $table,
            'where' => $where
        ]);
    }

    public function insert($table, $fieldsValues = [])
    {
        $mark = implode(",", array_fill(0, count($fieldsValues), '?'));
        $values = array_values($fieldsValues);
        $fields = implode(',', array_keys($fieldsValues));
        $query = "INSERT INTO {$table} ($fields) VALUES ({$mark})";
        $result = $this->exec($query, $values);
        return $result;
    }

    public function update()
    {

    }

    /**
     * @return mixed
     */
    public function getLastInsertId()
    {
        return $this->lastInsertId;
    }
}
