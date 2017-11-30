<?php

namespace ZXC;


abstract class Factory
{

    protected static $instances = [];

    abstract function reinitialize();

    public static function getInstance()
    {
        $params = func_get_args();
        $className = static::getClassName();
        if (!isset(self::$instances[$className])) {
            if ($params) {
                if (version_compare(PHP_VERSION, '5.6.0', '>=')) {
                    self::$instances[$className] = new $className(...$params);
                } else {
                    $reflect = new \ReflectionClass($className);
                    self::$instances[$className] = $reflect->newInstanceArgs($params);
                }
            } else {
                self::$instances[$className] = new $className();
            }
        }
        if ($params) {
            call_user_func_array([self::$instances[$className], 'reinitialize'], $params);
        }

        return self::$instances[$className];
    }

    public static function removeInstance()
    {
        $className = static::getClassName();
        if (array_key_exists($className, self::$instances)) {
            unset(self::$instances[$className]);
        }
    }

    final protected static function getClassName()
    {
        return get_called_class();
    }

    public function set($key, $value)
    {
        $this->$key = $value;
    }

    public function get($key)
    {
        if (isset($this->$key)) {
            return $this->$key;
        } else {
            return null;
        }
    }

    protected function __construct()
    {
    }

    final protected function __clone()
    {
    }

    final protected function __sleep()
    {
    }

    final protected function __wakeup()
    {
    }
}