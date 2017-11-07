<?php

namespace ZXC\Mod;
require_once ZXC_ROOT . DIRECTORY_SEPARATOR . 'ZXC' . DIRECTORY_SEPARATOR
    . 'Factory.php';
require_once ZXC_ROOT . DIRECTORY_SEPARATOR . 'ZXC' . DIRECTORY_SEPARATOR
    . 'Traits' . DIRECTORY_SEPARATOR . 'Helper.php';

use ZXC\Factory;
use ZXC\Traits\Helper;

class Autoload extends Factory
{
    use Helper;
    private static $autoloadDirectories = [];


    public static function getAutoloadDirectories()
    {
        return self::$autoloadDirectories;
    }

    public function setAutoloadDirectories( array $dir )
    {
        if ( !$this->isAssoc( $dir ) ) {
            return null;
        }
        self::$autoloadDirectories = array_merge(
            self::$autoloadDirectories, $dir
        );
    }

    public function disableAutoloadDirectories( $dir )
    {
        if ( isset( self::$autoloadDirectories[$dir] ) ) {
            self::$autoloadDirectories[$dir] = false;
            return true;
        }
        return false;
    }

    public function enableAutoloadDirectories( $dir )
    {
        if ( isset( self::$autoloadDirectories[$dir] ) ) {
            self::$autoloadDirectories[$dir] = true;
            return true;
        }
        return false;
    }

    public function removeAutoloadDirectories( $dir )
    {
        if ( isset( self::$autoloadDirectories[$dir] ) ) {
            unset( self::$autoloadDirectories[$dir] );
            return true;
        }
        return false;
    }

    public static function autoload( $className )
    {
        $file = str_replace( '\\', DIRECTORY_SEPARATOR, $className );
        if ( strpos( $className, 'ZXC' ) === 0 ) {
            $file = ZXC_ROOT . DIRECTORY_SEPARATOR . $file . '.php';
            if ( !is_file( $file ) ) {
                return false;
            }
        } else {
            if ( !empty( self::$autoloadDirectories ) ) {
                foreach ( self::$autoloadDirectories as $dir => $val ) {
                    if ( $val ) {
                        $file = ZXC_ROOT . DIRECTORY_SEPARATOR . $dir
                            . DIRECTORY_SEPARATOR . $file . '.php';
                        if ( is_file( $file ) ) {
                            break;
                        }
                    }
                }
            }
        }
        require $file;
        return false;
    }
}

spl_autoload_register( 'ZXC\Mod\Autoload::autoload' );
return Autoload::getInstance();