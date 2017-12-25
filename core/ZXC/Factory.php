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
        /**
         * If we create instance now we send params to construct
         */
        $createdNow = false;
        if (!isset(self::$instances[$className])) {
            $createdNow = true;
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
        /**
         * If we call Class::getInstance with arguments and class
         * was created before we call reinitialize with given arguments
         */
        if ($params && !$createdNow) {
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