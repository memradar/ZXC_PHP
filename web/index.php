<?php
$zxc = require_once '../core/index.php';
$config = require_once '../conf/config.php';

$routes = [
    [
        'route' => 'POST|/test/:route/',
        'call' => function ( $zxc ) {
            $stop = $zxc;
        }
    ]
];
$zxc->registerRoutes( $routes );
$zxc->go();


////$zxc->registerConfig($config);
//$db = new ZXC\DB( 'pgsql:host=localhost;dbname=testusers', 'postgres', '123456' );
//$r = $db->getDb();
//$stmt = $r->prepare( 'select column_name as name from information_schema.columns where table_name=?;' );
//$stmt->execute( [ 'users' ] );
//$res = $stmt->fetchAll();
//$zxc->set( 'asdf', 1234 );
//$w = $zxc->get( 'asdf' );
//$w1 = $zxc->get( 'asdf1' );
//
//preg_match_all( '/(([\w\/:]*)+[^|:])/', $routes[0]['route'], $matches, PREG_PATTERN_ORDER );
//$stop = $matches;
//$auth = ZXC\Mod\Auth::getInstance();
//$auth->set( 'test', function ( $k ) {
//    $stop = $k;
//} );
//$func = $auth->get( 'test' );
//$func( '123' );
//$auth2 = ZXC\Mod\Auth::getInstance();