<?php
namespace ZXC;

class Router extends Factory
{
    private $routes = [];
    private $routeTypes
        = [
            'POST' => true,
            'GET'  => true
        ];

    public function registerRoutes( $routes = [] )
    {
        if ( $routes ) {
            foreach ( $routes as $route ) {
                if ( isset( $route['route'] ) ) {
                    $parsedRoute = $this->parseRoute( $route );
                    if ( isset( $this->routeTypes[$parsedRoute['type']] )
                        && $this->routeTypes[$parsedRoute['type']] === true
                    ) {
                        $this->routes[$parsedRoute['type']][$parsedRoute['route']]
                            = $parsedRoute;
                    }
                }
            }
        }
    }

    private function parseRoute( $route )
    {
        //TODO regexp
//        preg_match_all( '/(([\w\/\\:]*)+[^|:])/', $route['route'], $params, PREG_PATTERN_ORDER );
        $params = explode( '|', $route['route'] );
        if ( !$params || count( $params ) < 2 ) {
            throw new \Exception(
                'Route is not valid! Must be like this \'POST|/test/:route/|Class:method\''
            );
        }
        $classAndMethod = [];
        if ( isset( $params[2] ) ) {
            $classAndMethod = explode( ':', $params[2] );
        }
        return [
            'type'   => $params[0],
            'route'  => $params[1],
            'reg'    => $this->getRegex( $params[1] ),
            'class'  => isset( $classAndMethod[0] ) ? $classAndMethod[0] : null,
            'method' => isset( $classAndMethod[1] ) ? $classAndMethod[1] : null,
            'func'   => isset( $route['call'] ) ? $route['call'] : null,
            'params' => null
        ];
    }

    public function disableRouterType( $type )
    {
        $type = strtoupper( $type );
        if ( isset( $this->routeTypes[$type] ) ) {
            $this->routeTypes[$type] = false;
            return true;
        }
        return false;
    }

    public function getCurrentRoutParams( $uri, $base, $method )
    {
        if ( !isset( $this->routes[$method] ) ) {
            return null;
        }
        if ( $base != '/' ) {
            $path = substr( $uri, strlen( $base ) );
        } else {
            $path = $uri;
        }

        foreach ( $this->routes[$method] as &$route ) {
            $ok = preg_match( $route['reg'], $path, $matches );
            if ( $ok ) {
                $params = array_intersect_key(
                    $matches,
                    array_flip(
                        array_filter(
                            array_keys( $matches ),
                            'is_string'
                        )
                    )
                );
                if ( $params ) {
                    $route['params'] = $params;
                }
                return $route;
            }
        }
        return null;
    }

    /**
     * @param $pattern
     *
     * @return bool|string
     * Thanks https://stackoverflow.com/questions/30130913/how-to-do-url-matching-regex-for-routing-framework/30359808#30359808
     */
    private function getRegex( $pattern )
    {
        if ( preg_match( '/[^-:\/_{}()a-zA-Z\d]/', $pattern ) ) {
            return false;
        } // Invalid pattern

        // Turn "(/)" into "/?"
        $pattern = preg_replace( '#\(/\)#', '/?', $pattern );

        // Create capture group for ":parameter"
        $allowedParamChars = '[a-zA-Z0-9\_\-]+';
        $pattern = preg_replace(
            '/:(' . $allowedParamChars . ')/',   # Replace ":parameter"
            '(?<$1>' . $allowedParamChars . ')',
            # with "(?<parameter>[a-zA-Z0-9\_\-]+)"
            $pattern
        );

        // Create capture group for '{parameter}'
        $pattern = preg_replace(
            '/{(' . $allowedParamChars . ')}/',    # Replace "{parameter}"
            '(?<$1>' . $allowedParamChars . ')',
            # with "(?<parameter>[a-zA-Z0-9\_\-]+)"
            $pattern
        );

        // Add start and end matching
        $patternAsRegex = "@^" . $pattern . "$@D";

        return $patternAsRegex;
    }

    public function __construct()
    {

    }
}