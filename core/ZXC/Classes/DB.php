<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 04.10.2017
 * Time: 16:59
 */

namespace ZXC\Classes;

/**
 * Class DB
 * @package ZXC\Mod
 * @property db PDO
 */
class DB
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
     * @param array $params
     * @throws \Exception
     */
    public function __construct(array $params = [])
    {
        $this->initialize($params);
    }

    /**
     * Initialize PDO instance
     * @param array $params
     * @throws \Exception
     */
    public function initialize(array $params = [])
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

        try {
            $this->db = new \PDO($this->dns, $this->name, $this->pass,
                [\PDO::ATTR_PERSISTENT => $this->persistent]);
            $this->dbType = $this->db->getAttribute(\PDO::ATTR_DRIVER_NAME);
            $this->db->setAttribute(\PDO::ATTR_ERRMODE,
                \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            //TODO Exception
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
     * Get PDO instance
     * @return null|\PDO
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * Get DB type
     * @return mixed|null
     */
    public function getDbType()
    {
        return $this->dbType;
    }

    /**
     * Execute query
     * @param string $query
     * @param array $params
     * @param int $fetchStyle
     * @return array|bool
     */
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

    /**
     * Get all columns fom table
     * @param $tableSchema
     * @param $tableName
     * @param int $fetchStyle
     * @return array|object
     */
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

    /**
     * Execute action
     * @param array $object
     * @return array|bool
     */
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

    /**
     * Select records from table
     * @param string $table = schema.table || table
     * @param string $fields
     * @param array $where
     * @return array|bool
     */
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

    /**
     * Delete record from table
     * @param string $table = schema.table || table
     * @param array $where = ['id', '=', 1]
     * @return array|bool
     */
    public function delete($table, $where = [])
    {
        if (!$table || !$where) {
            throw new \InvalidArgumentException();
        }
        return $this->execAction([
            'action' => 'delete',
            'fields' => '',
            'table' => $table,
            'where' => $where
        ]);
    }

    /**
     * Insert records into table
     * @param string $table = schema.table || table
     * @param array $fieldsValues = [
     *      'login' => 'login',
     *      'email' => 'email'
     * ]
     * @return array|bool
     */
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
     * Get last inserted id
     * @return mixed
     */
    public function getLastInsertId()
    {
        return $this->lastInsertId;
    }
}
