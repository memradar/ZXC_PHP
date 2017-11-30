<?php
require_once 'Foo.php';
require_once 'Foo2.php';
/*
Применение
*/


$foo = Foo::getInstance(['asdfasdfasd'=>'aaaaasssssssdddddd']);
$foo->incBar();

var_dump($foo->getBar());

$foo = Foo::getInstance();
$foo->incBar();

var_dump($foo->getBar());


$foo2 = Foo2::getInstance();
$foo2->incBar();

var_dump($foo2->getBar());

$foo2 = Foo2::getInstance();
$foo2->incBar();

var_dump($foo2->getBar());