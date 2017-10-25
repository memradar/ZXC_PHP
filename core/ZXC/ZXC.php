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
    private $router;
    private $main = [];

    protected function __construct() {
        $this->router = Router::getInstance( 'fds' );
        $this->fillMain();
    }

    public function registerRoutes( $routes = [] ) {
        if ( !$this->router || !$routes ) {
            return false;
        }
        $this->router->registerRoutes( $routes );
        return true;
    }

    private function fillMain() {
        $this->main['URI'] = &$_SERVER['REQUEST_URI'];
        $this->main['HOST'] = $_SERVER['SERVER_NAME'];
        $this->main['METHOD'] = $_SERVER['REQUEST_METHOD'];
        $this->main['BASE_ROUTE'] = dirname( $_SERVER['SCRIPT_NAME'] );
    }

    public function go() {

    }
}

spl_autoload_register( [ 'ZXC\ZXC', 'autoload' ], true, true );
