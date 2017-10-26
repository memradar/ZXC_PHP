<?php
return $config = [
    'DB' => [
        'HOST' => 'localhost',
        'PORT' => 5432,
        'NAME' => 'postgres',
        'PASS' => '123456',
        'LOCAL' => ''
    ],
    'AUTH' => [
        'field' => [ 'login', 'password' ]
    ],
    'ZXC'=>[
        'version'=> '0.0.1-a'
    ],
    'AUTOLOAD'=>'../AUTOL'
];