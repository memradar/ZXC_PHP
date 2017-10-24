<?php
$zxc = require_once '../core/index.php';
$config = require_once '../conf/config.php';
//$zxc->registerConfig($config);
$db = new ZXC\DB( 'pgsql:host=localhost;dbname=testusers', 'postgres', '123456' );
$zxc->set( 'asdf', 1234 );
$w = $zxc->get( 'asdf' );
$w1 = $zxc->get( 'asdf1' );

$routes = [
    [
        'route' => 'POST|/test/:route/|Class:method',
        'call' => function ( $zxc ) {
            $stop = $zxc;
        }
    ]
//    [
//        'route' => 'POST/test/:route/Class:method',
//        'call' => function ( $zxc ) {
//            $stop = $zxc;
//        }
//    ]
];
$zxc->registerRoutes($routes);
preg_match_all( '/(([\w\/:]*)+[^|:])/', $routes[0]['route'], $matches,PREG_PATTERN_ORDER );
$stop = $matches;
$auth = ZXC\Mod\Auth::getInstance();
$auth->set( 'test', function ( $k ) {
    $stop = $k;
} );
$func = $auth->get( 'test' );
$func( '123' );
$auth2 = ZXC\Mod\Auth::getInstance();