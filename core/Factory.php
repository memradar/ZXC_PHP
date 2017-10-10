<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 04.10.2017
 * Time: 16:47
 */

namespace ZXC;


abstract class Factory {

    protected static $instances = [];

    public static function getInstance() {
        $className = static::getClassName();
        if ( !( self::$instances[$className] instanceof $className ) ) {
            self::$instances[$className] = new $className();
        }
        return self::$instances[$className];
    }

    public static function removeInstance() {
        $className = static::getClassName();
        if ( array_key_exists( $className, self::$instances ) ) {
            unset( self::$instances[$className] );
        }
    }

    final protected static function getClassName() {
        return get_called_class();
    }

    protected function __construct() {
    }

    final protected function __clone() {
    }

    final protected function __sleep() {
    }

    final protected function __wakeup() {
    }
}