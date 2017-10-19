<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 04.10.2017
 * Time: 16:56
 */

namespace ZXC;

require_once 'Factory.php';

class ZXC extends Factory {
    protected $routes = [];

    protected function __construct($params) {

    }

    public function set( $key, $val ) {
        $this[$key] = $val;
    }

    public function get( $key ) {

    }
}

spl_autoload_register( [ 'ZXC\ZXC', 'autoload' ], true, true );
