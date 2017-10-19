<?php
$zxc = require_once '../core/index.php';
$config = require_once '../conf/config.php';
$zxc->registerConfig($config);
$db = new ZXC\DB('pgsql:host=localhost;dbname=testusers', 'postgres', '123456');
$auth = ZXC\Mod\Auth::getInstance();
$auth2 = ZXC\Mod\Auth::getInstance();