<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 24.10.2017
 * Time: 17:08
 */

namespace ZXC;

class Router extends Factory {
    private $routes = [];
    private $routeTypes = [
        'POST' => true,
        'GET' => true
    ];

    public function registerRoutes( $routes = [] ) {
        if ( $routes ) {
            foreach ( $routes as $route ) {
                if ( isset( $route['route'] ) ) {
                    $parsedRoute = $this->parseRoute( $route );
                    if ( isset( $this->routeTypes[$parsedRoute['type']] ) && $this->routeTypes[$parsedRoute['type']] === true ) {
                        $this->routes[$parsedRoute['type']][$parsedRoute['route']] = $parsedRoute;
                    }
                }
            }
        }
    }

    private function parseRoute( $route ) {
        preg_match_all( '/(([\w\/:]*)+[^|:])/', $route['route'], $params, PREG_PATTERN_ORDER );
        if ( !$params || count( $params ) < 2 ) {
            throw new \Exception( 'Route is not valid! Must be like this \'POST|/test/:route/|Class:method\'' );
        }
        $classAndMethod = [];
        if ( isset( $params[0][2] ) ) {
            $classAndMethod = explode( isset( $params[0][2] ), ':' );
        }
        return [
            'type' => $params[0][0],
            'route' => $params[0][1],
            'class' => isset( $classAndMethod[0] ) ? $classAndMethod[0] : null,
            'method' => isset( $classAndMethod[1] ) ? $classAndMethod[1] : null,
            'func' => isset( $route['call'] ) ? $route['call'] : null
        ];
    }

    public function disableRouterType( $type ) {
        $type = strtoupper( $type );
        if ( isset( $this->routeTypes[$type] ) ) {
            $this->routeTypes[$type] = false;
            return true;
        }
        return false;
    }

    public function __construct( $r ) {
        $this->as = $r;
    }
}