<?php
$zxc = require_once '../core/index.php';
$config = require_once '../conf/config.php';
$zxc->set( 'AUTOLOAD', '../AUTOL' );
//$asd = new \ASD\Test();
//echo $asd->qwe();
$routes = [
    [
        'route' => 'GET|/test/:username/|Test:met',
        'call' => function ( $zxc ) {
            $stop = $zxc;
        }
    ],
    [
        'route' => 'POST|/:test/:username/:profile/:pro|Test:met',
        'call' => function ( $zxc ) {
            $stop = $zxc;
        }
    ],
    [
        'route' => 'POST|/:test|ASD\Test:qwe',
        'call' => function ( $zxc ) {
            $stop = $zxc;
        }
    ],
    [
        'route' => 'GET|/test1/route/',
        'call' => function ( $zxc ) {
            $stop = $zxc;
        }
    ]
];
$zxc->registerRoutes( $routes );
$zxc->go();