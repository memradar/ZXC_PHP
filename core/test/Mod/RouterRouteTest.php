<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 14/12/2017
 * Time: 23:13
 */
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

define('ZXC_ROOT_TEST_DIR', __DIR__);

class RoutesTest
{
    public function main(\ZXC\ZXC $zxc, $params = null)
    {
        if (!$params['resultBefore']) {
            throw new \InvalidArgumentException();
        }
        return 'main' . $params['resultBefore'];
    }

    public function before(\ZXC\ZXC $zxc, $params)
    {
        if ($params['routeParams'] !== null) {
            throw new \InvalidArgumentException();
        }
        return 'before';
    }

    public function before2(\ZXC\ZXC $zxc, $params)
    {
        if ($params['routeParams'] !== null) {
            throw new \InvalidArgumentException();
        }
        return 'before';
    }

    public function after(\ZXC\ZXC $zxc, $params)
    {
        if (!$params['resultMain'] || $params['resultMain'] !== 'mainbefore') {
            throw new \InvalidArgumentException();
        }
        return 'after' . $params['resultMain'];
    }
}

class RouterRouteTest extends TestCase
{
    /**
     * @var \ZXC\ZXC
     */
    private $zxc;
    /**
     * @var \ZXC\Mod\Route
     */
    private $route;

    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->getZXC();
    }

    /**
     * Set test class variables $config and $zxc
     */
    public function getZXC()
    {

        $config = $this->config = require_once ZXC_ROOT_TEST_DIR . '/../config/config.php';
        require_once ZXC_ROOT_TEST_DIR . '/../../ZXC/../index.php';
        $this->zxc = \ZXC\ZXC::getInstance();
    }

    public function testRouterInstanceWithoutParameters()
    {
        $this->expectException(InvalidArgumentException::class);
        new \ZXC\Mod\Route();
    }

    public function testRouterInstanceWithParameters()
    {
        $params = [
            'type' => 'GET',
            'route' => '/',
            'reg' => '@^/$@D',
            'class' => 'QWEQ',
            'method' => 'qwe',
            'func' => function () {
            },
            'before' => ['class' => 'QWEQ', 'method' => 'before'],
            'after' => function () {
            },
            'children' => [],
            'hooksResultTransfer' => true
        ];
        $this->route = new \ZXC\Mod\Route($params);
        $this->assertEquals($this->route->getType(), $params['type']);
        $this->assertEquals($this->route->getRoute(), $params['route']);
        $this->assertEquals($this->route->getReg(), $params['reg']);
        $this->assertEquals($this->route->getClass(), $params['class']);
        $this->assertEquals($this->route->getMethod(), $params['method']);
        $this->assertEquals($this->route->getFunc(), $params['func']);
        $this->assertEquals($this->route->getParams(), null);
        $this->assertEquals($this->route->getBefore(), ['class' => 'QWEQ', 'method' => 'before']);
        $this->assertEquals($this->route->getAfter(), $params['after']);
        $this->assertEquals($this->route->getChildren(), $params['children']);
        $this->assertEquals($this->route->getHooksResultTransfer(), $params['hooksResultTransfer']);
    }

    public function testRouterWithoutParams()
    {
        $Router = \ZXC\Mod\Router::getInstance();
        $this->expectException(InvalidArgumentException::class);
        $Router->reinitialize();
    }

    public function testRouterClass()
    {
        $routesParams = [
            [
                'route' => 'GET|/|RoutesTest:main',
                'call' => function ($zxc) {
                    $stop = $zxc;
                },
                'before' => 'RoutesTest:before',
                'after' => 'RoutesTest:after',
                'hooksResultTransfer' => true
            ],
            [
                'route' => 'GET|/:user',
                'call' => function (\ZXC\ZXC $zxc, $params = null) {
                    if ($params['resultBefore'] !== 'before') {
                        throw new \InvalidArgumentException();
                    }
                    return 'main' . $params['resultBefore'];
                },
                'before' => 'RoutesTest:before2',
                'after' => function (\ZXC\ZXC $zxc, $params = null) {
                    if (!$params['resultMain'] || $params['resultMain'] !== 'mainbefore') {
                        throw new \InvalidArgumentException();
                    }
                },
                'hooksResultTransfer' => true,
                'children' => [
                    'route' => 'GET|profile|QWEQ:profile',
                    'before' => 'QWEQ:profileBefore',
                    'after' => 'QWEQ:profileAfter',
                    'children' => [
                        'route' => 'POST|profile2|QWEQ:profile2',
                        'before' => 'QWEQ:profileBefore2',
                        'after' => 'QWEQ:profileAfter2',
                    ]
                ]
            ],
            [
                'route' => 'POST|/:user|QWEQ:user',
                'call' => function ($zxc) {
                    $stop = $zxc;
                },
                'before' => 'ASD\TestClass:before',
                'after' => function ($z, $p, $result) {
                    $zxc = $z;
                    $params = $p;
                    echo 'after hooks=>' . $result;
                },
                'hooksResultTransfer' => true,
                'children' => [
                    'route' => 'GET|profile|QWEQ:profile',
                    'before' => 'QWEQ:profileBefore',
                    'after' => 'QWEQ:profileAfter',
                    'children' => [
                        'route' => 'POST|profile2|QWEQ:profile2',
                        'before' => 'QWEQ:profileBefore2',
                        'after' => 'QWEQ:profileAfter2',
                    ]
                ]
            ]
        ];
        /**
         * @var $Router \ZXC\Mod\Router
         */
        $Router = \ZXC\Mod\Router::getInstance();
        $Router->reinitialize($routesParams);
        $routes = $Router->getRoutes();
        $this->assertEquals(count($routes), 2);
        $this->assertEquals(count($routes['GET']), 3);
        $this->assertEquals(count($routes['POST']), 2);
        $routeTypes = $Router->getRouteTypes();
        $this->assertEquals(count($routeTypes['POST']), true);
        $this->assertEquals(count($routeTypes['GET']), true);
        $routes['GET']['/']->executeRoute($this->zxc);
        $routes['GET']['/:user']->executeRoute($this->zxc);
    }
}