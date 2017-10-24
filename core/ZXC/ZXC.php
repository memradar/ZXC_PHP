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
    private $router = null;

    protected function __construct() {
        $this->router = Router::getInstance();
    }

    public function registerRoutes( $routes = [] ) {
        if ( !$this->router || !$routes ) {
            return false;
        }
        $this->router->registerRoutes( $routes );
        return true;
    }
}

spl_autoload_register( [ 'ZXC\ZXC', 'autoload' ], true, true );
