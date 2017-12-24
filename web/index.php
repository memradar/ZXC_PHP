<?php

//function exception_error_handler($errno, $errstr, $errfile, $errline ) {
//    throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
//}
//set_error_handler("exception_error_handler");

$zxc = require_once '../core/index.php';
$config = require_once '../conf/config.php';
$zxc->set('CONFIG', $config);
$zxc->set('AUTOLOAD', ['../autoloadtest' => true, '/' => true]);
$routes = [
    [
        'route' => 'GET|/:username|ASD\TestClass:qwe',
        'call' => function ($zxc) {
            $stop = $zxc;
        },
        'before' => 'ASD\TestClass:qwe',
        'after' => function ($z, $p) {
            $zxc = $z;
            $params = $p;
        }
    ],
    [
        'route' => 'POST|/:user/profile|ASD\TestClass:asd',
        'call' => function ($zxc) {
            $stop = $zxc;
        }
    ],
    [
        'route' => 'GET|/',
        'call' => function () {
            echo 'main route';
        }
    ]
];
$zxc->registerRoutes($routes);
$zxc->go();