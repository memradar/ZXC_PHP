<?php
$zxc = require_once '../core/index.php';
$config = require_once '../conf/config.php';
$zxc->set( 'AUTOLOAD', '../autoloadtest' );
$routes = [
    [
        'route' => 'POST|/:username|ASD\TestClass:qwe',
        'call' => function ( $zxc ) {
            $stop = $zxc;
        },
        'before' => 'ASD\TestClass:qwe',
        'after' => function ( $z, $p ) {
            $zxc = $z;
            $params = $p;
        }
    ],
    [
        'route' => 'POST|/:user/profile|ASD\TestClass:asd',
        'call' => function ( $zxc ) {
            $stop = $zxc;
        }
    ],
    [
        'route' => 'GET|/|ASD\TestClass:qget',
        'call' => function () {
            echo 'main route';
        }
    ],
    [
        'route' => 'POST|/|ASD\TestClass:qpost',
        'call' => function () {
            echo 'main route POST';
        }
    ]
];
$zxc->registerRoutes( $routes );
$zxc->go();