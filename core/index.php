<?php
define('ZXC_ROOT', __DIR__);
define('APP_ROOT', $_SERVER['DOCUMENT_ROOT']);
require_once 'ZXC/ZXC.php';
$zxc = \ZXC\ZXC::getInstance();
$zxc->initialize($config);
return $zxc;