<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 04.10.2017
 * Time: 16:56
 */

namespace ZXC;

require_once 'Factory.php';


class ZXC extends Factory
{
    private $router;
    private $main = [];

    protected function __construct()
    {
        spl_autoload_register( [ 'ZXC\ZXC', 'autoload' ], true, true );
        $this->fillMain();
        $this->router = Router::getInstance( 'fds' );
    }

    public function registerRoutes( $routes = [] )
    {
        if ( !$this->router || !$routes ) {
            return false;
        }
        $this->router->registerRoutes( $routes );
        return true;
    }

    private function fillMain()
    {
        $this->main['URI'] = &$_SERVER['REQUEST_URI'];
        $this->main['HOST'] = $_SERVER['SERVER_NAME'];
        $this->main['METHOD'] = &$_SERVER['REQUEST_METHOD'];
        $this->main['BASE_ROUTE'] = dirname( $_SERVER['SCRIPT_NAME'] );
        $this->main['ROUTE'] = '';
        $this->main['POST'] = &$_POST;
        $this->main['GET'] = &$_GET;
        $this->main['AUTOLOAD'] = '';
    }

    public function set( $key, $val )
    {
        if ( isset( $this->main, $key ) ) {
            $this->main[$key] = $val;
        }
    }

    public function go()
    {
        $routeParams = $this->router->getCurrentRoutParams( $this->main['URI'], $this->main['BASE_ROUTE'], $this->main['METHOD'] );
        if ( !$routeParams ) return false; //TODO 404

        if ( !$routeParams['class'] && !$routeParams['func'] && !is_callable( $routeParams['func'] ) ) {
            return false; //TODO 404
        }
        ob_start();
        if ( $routeParams['class'] ) {
            if ( is_subclass_of( $routeParams['class'], 'ZXC\Factory', true ) ) {
                $userClass = call_user_func( $routeParams['class'] . '::getInstance' );
                call_user_func_array( [ $userClass, $routeParams['method'] ], [ $this, $routeParams['params'] ] );
            } else {
                if ( class_exists( $routeParams['class'] ) ) {
                    $userClass = new $routeParams['class'];
                    if ( method_exists( $userClass, $routeParams['method'] ) ) {
                        call_user_func_array( [ $userClass, $routeParams['method'] ], [ $this, $routeParams['params'] ] );
                    }
                }
            }
        } else {
            call_user_func_array( $routeParams['func'], [ $this, $routeParams['params'] ] );
        }

        $body = ob_get_clean();
        echo $body;
        return true;
    }

    public function autoload( $className )
    {
        $file = str_replace( '\\', DIRECTORY_SEPARATOR, $className );
        if ( strpos( $className, 'ZXC' ) === 0 ) {
            $file = ZXC_ROOT . DIRECTORY_SEPARATOR . $file . '.php';
        } else {
            $file = ZXC_ROOT . DIRECTORY_SEPARATOR . $this->main['AUTOLOAD'] . DIRECTORY_SEPARATOR . $file . '.php';
        }
        if ( is_file( $file ) ) require $file;
    }
}