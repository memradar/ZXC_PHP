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
                    preg_match_all( '/(([\w\/:]*)+[^|:])/', $route['route'], $params, PREG_PATTERN_ORDER );
                    if ( !$params || count( $params ) < 2 ) {
                        throw new \Exception( 'Route is not valid! Must be like this \'POST|/test/:route/|Class:method\'' );
                    }
                    $stop = $params;
                    $this->routes['df'] = 1;
                }
            }
        } else {
            $stop = false;
        }
    }

    public function disableRouterType() {

    }
}