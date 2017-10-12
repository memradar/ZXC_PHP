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
        if ( !isset( self::$instances[$className] ) ) {
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

    public static function autoload( $className ) {
        $file = str_replace('\\', DIRECTORY_SEPARATOR, $className);
        $file = ZXC_ROOT .DIRECTORY_SEPARATOR. $file .'.php';
        require_once $file;
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