<?php
define('ZXC_ROOT', __DIR__);
define('APP_ROOT', $_SERVER['DOCUMENT_ROOT']);
require_once 'ZXC/ZXC.php';
return \ZXC\ZXC::getInstance($config);