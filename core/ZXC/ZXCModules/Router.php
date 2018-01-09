<?php

namespace ZXC\ZXCModules;

use ZXC\Patterns\Singleton;

class Router
{
    use Singleton;
    private $routes = [];
    private $routeTypes = ['POST' => true, 'GET' => true];

    /**
     * Initialize router
     * @param array $config [
     *      [
     *          'route' => 'GET|/api|Class:method',
     *          'call' => function ($zxc) { },
     *          'before' => 'Class:before',
     *          'after' => 'Class:after'
     *      ]
     *  ]
     */
    public function initialize(array $config = [])
    {
        $params = func_get_args();
        if (!$params) {
            throw new \InvalidArgumentException('Undefined $params');
        }
        foreach ($params as $routes) {
            foreach ($routes as $route) {
                if (isset($route['route'])) {
                    $this->parseRoute($route);
                }
            }
        }
    }

    /**
     * Parse hooks
     * @param string $params 'Class:before from route params ['before' => 'Class:before']
     * @return array|bool
     */
    private function parseHooks($params = '')
    {
        if (!$params) {
            throw new \InvalidArgumentException('Undefined $params');
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

    /**
     * Set parsed route in $this->routes type(GET,POST)
     * @param Route $parsedRoute
     */
    private function setRoute(Route $parsedRoute)
    {
        $parsedRouteType = $parsedRoute->getType();
        if (isset($this->routeTypes[$parsedRouteType]) && $this->routeTypes[$parsedRouteType] === true) {
            $this->routes[$parsedRouteType][$parsedRoute->getRoute()] = $parsedRoute;
        }
    }

    /**
     * Parse route from config
     * @param array $route
     *  [
     *      'route' => 'GET|/api|Class:method',
     *      'call' => function ($zxc) { },
     *      'before' => 'Class:before',
     *      'after' => 'Class:after'
     *  ]
     * @return bool
     */

    private function parseRoute(array $route)
    {
        $params = explode('|', $route['route']);
        if (!$params || count($params) < 2) {
            throw new \InvalidArgumentException('Undefined $params');
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
            'children' => isset($route['children']) ? $route['children'] : null,
            'hooksResultTransfer' => isset($route['hooksResultTransfer']) ? $route['hooksResultTransfer'] : null
        ];

        $route = new Route($params);
        $this->setRoute($route);
        $children = $route->getChildren();
        if ($children) {
            $childParams = explode('|', $children['route']);
            if (count($childParams) < 2) {
                //TODO invalid parameters
                return null;
            } else {
                if (count($childParams) === 2) {
                    $slash = substr($children['route'], 0, 1);
                    if ($slash !== '/') {
                        $children['route'] = $route->getType() . '|' . $route->getRoute() . '/' . $children['route'];
                    }
                } else {
                    $childType = $childParams[0];
                    $path = $childParams[1];
                    $haveParent = strpos($path, $route->getRoute());
                    if (!$haveParent) {
                        $slash = substr($path, 0, 1);
                        if ($slash !== '/') {
                            $path = $route->getRoute() . '/' . $path;
                            $children['route'] = $childType . '|' . $path . '|' . $childParams[2];
                        }
                    }
                }
                $this->parseRoute($children);
            }
        }
        return true;
    }

    /**
     * Disable router type
     * @param string $type (GET,POST)
     * @return bool
     */
    public function disableRouterType($type)
    {
        $type = strtoupper($type);
        if (isset($this->routeTypes[$type])) {
            $this->routeTypes[$type] = false;

            return true;
        }

        return false;
    }

    /**
     * Get router parameters for URI
     * @param string $uri http->getPath
     * @param string $base http->getBaseRoute
     * @param string $method http->getMethod
     * @return bool|Route
     */
    public function getCurrentRoutParams($uri, $base, $method)
    {
        if (!$uri || !$base || !$method) {
            throw new \InvalidArgumentException('Undefined $params');
        }
        if (!isset($this->routes[$method])) {
            return false;
        }
        if ($base != '/') {
            $path = substr($uri, strlen($base));
        } else {
            $path = $uri;
        }
        /**
         * @var $route Route
         */
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

        return false;
    }

    /**
     * @param $pattern
     *
     * @return bool|string
     * @link Thanks https://stackoverflow.com/questions/30130913/how-to-do-url-matching-regex-for-routing-framework/30359808#30359808
     */
    private function getRegex($pattern)
    {
        if (preg_match('/[^-:\/_{}()a-zA-Z\d]/', $pattern)) {
            throw new \InvalidArgumentException('Invalid pattern');
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

    /**
     * Returns all registered routes
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Returns all registered route types
     * @return array
     */
    public function getRouteTypes(): array
    {
        return $this->routeTypes;
    }
}