<?php

namespace ZXC\Mod;

use ZXC\Factory;
use ZXC\Mod\Logger;

class Router extends Factory
{
    private $routes = [];
    private $routeTypes = ['POST' => true, 'GET' => true];

    public function reinitialize()
    {
        $params = func_get_args();
        foreach ($params as $item) {
            $this->registerRoutes($item);
        }
    }

    public function registerRoutes(array $routes = [])
    {
        if ($routes) {
            foreach ($routes as $route) {
                if (isset($route['route'])) {
                    $parsedRoute = $this->parseRoute($route);
                    $parsedRouteType = $parsedRoute->getType();
                    if (isset($this->routeTypes[$parsedRouteType]) && $this->routeTypes[$parsedRouteType] === true) {
                        $this->routes[$parsedRouteType][$parsedRoute->getRoute()] = $parsedRoute;
                    }
                }
            }
        }
    }

    public function parseHooks($params = '')
    {
        if (!$params) {
            return false;
        }
        $classAndMethod = explode(':', $params);
        if (!$classAndMethod || count($classAndMethod) > 2) {
            return false;
        }
        return [
            'class' => $classAndMethod[0],
            'method' => $classAndMethod[1]
        ];
    }

    private function parseRoute($route)
    {
        //TODO regexp
        $params = explode('|', $route['route']);
        if (!$params || count($params) < 2) {
            return false;
//            throw new \Exception(
//                'Route is not valid! Must be like this \'POST|/test/:route/|Class:method\''
//            );
        }
        $classAndMethod = [];
        if (isset($params[2])) {
            $classAndMethod = explode(':', $params[2]);
        }
        $before = null;
        $after = null;
        if (isset($route['before'])) {
            if (is_callable($route['before'])) {
                $before = $route['before'];
            } else {
                $before = $this->parseHooks($route['before']);
            }
        }
        if (isset($route['after'])) {
            if (is_callable($route['after'])) {
                $after = $route['after'];
            } else {
                $after = $this->parseHooks($route['after']);
            }
        }

        $params = [
            'type' => $params[0],
            'route' => $params[1],
            'reg' => $this->getRegex($params[1]),
            'class' => isset($classAndMethod[0]) ? $classAndMethod[0] : null,
            'method' => isset($classAndMethod[1]) ? $classAndMethod[1] : null,
            'func' => isset($route['call']) ? $route['call'] : null,
            'before' => $before,
            'after' => $after,
            'hooksResultTransfer' => isset($route['hooksResultTransfer']) ? $route['hooksResultTransfer'] : null
        ];
        return new Route($params);
    }

    public function disableRouterType($type)
    {
        $type = strtoupper($type);
        if (isset($this->routeTypes[$type])) {
            $this->routeTypes[$type] = false;

            return true;
        }

        return false;
    }

    public function getCurrentRoutParams($uri, $base, $method)
    {
        if (!isset($this->routes[$method])) {
            return null;
        }
        if ($base != '/') {
            $path = substr($uri, strlen($base));
        } else {
            $path = $uri;
        }

        foreach ($this->routes[$method] as $route) {
            $ok = preg_match($route->getReg(), $path, $matches);
            if ($ok) {
                $params = array_intersect_key(
                    $matches,
                    array_flip(
                        array_filter(
                            array_keys($matches),
                            'is_string'
                        )
                    )
                );
                if ($params) {
                    $route->setParams($params);
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
    private function getRegex($pattern)
    {
        if (preg_match('/[^-:\/_{}()a-zA-Z\d]/', $pattern)) {
            return false;
        } // Invalid pattern

        // Turn "(/)" into "/?"
        $pattern = preg_replace('#\(/\)#', '/?', $pattern);

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
//        $loger = new Logger(['filePath' => '../conf/router.log', 'root' => true]);
//        $loger->info('router ->>>>>>> test message', ['testContext' => 'qwerty']);
    }
}