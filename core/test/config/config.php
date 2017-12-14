<?php

return $config = [

    'ZXC\Mod' => [
        'DB' => [
            'db' => 'hs',
            'dbtype' => 'pgsql',
            'dns' => 'pgsql:host=localhost;dbname=hs;port=5433',
            'host' => 'localhost',
            'port' => 5433,
            'name' => 'postgres',
            'pass' => '123456',
            'local' => '',
            'persistent' => false
        ],
        'Auth' => [
            'field' => [
                'login',
                'password'
            ]
        ],
        'Autoload' => [
            '../../hs' => true,
            '' => true
        ],
        'Logger' => [
            'applevel' => 'debug',
            'settings' => [
                'filePath' => '../../log/log.log',
                'root' => true
            ]
        ],
        'HTTP' => ['fadsfa'],
        'Server'=>[],
        'Router' => [
            [
                'route' => 'GET|/|QWEQ:qwe',
                'call' => function ($zxc) {
                    $stop = $zxc;
                },
                'before' => 'QWEQ:before',
                'after' => function ($z, $p) {
                    $zxc = $z;
                    $params = $p;
                }
            ],
            [
                'route' => 'GET|/:user|QWEQ:user',
                'call' => function ($zxc) {
                    $stop = $zxc;
                },
                'before' => 'ASD\TestClass:before',
                'after' => function ($z, $p, $result) {
                    $zxc = $z;
                    $params = $p;
                    echo 'after hooks=>' . $result;
                },
                'hooksResultTransfer' => true,
                'children' => [
                    'route' => 'GET|profile|QWEQ:profile',
                    'before' => 'QWEQ:profileBefore',
                    'after' => 'QWEQ:profileAfter',
                    'children' => [
                        'route' => 'POST|profile2|QWEQ:profile2',
                        'before' => 'QWEQ:profileBefore2',
                        'after' => 'QWEQ:profileAfter2',
                    ]
                ]
            ],[
                'route' => 'POST|/:user|QWEQ:user',
                'call' => function ($zxc) {
                    $stop = $zxc;
                },
                'before' => 'ASD\TestClass:before',
                'after' => function ($z, $p, $result) {
                    $zxc = $z;
                    $params = $p;
                    echo 'after hooks=>' . $result;
                },
                'hooksResultTransfer' => true,
                'children' => [
                    'route' => 'GET|profile|QWEQ:profile',
                    'before' => 'QWEQ:profileBefore',
                    'after' => 'QWEQ:profileAfter',
                    'children' => [
                        'route' => 'POST|profile2|QWEQ:profile2',
                        'before' => 'QWEQ:profileBefore2',
                        'after' => 'QWEQ:profileAfter2',
                    ]
                ]
            ]
        ]
    ]
];