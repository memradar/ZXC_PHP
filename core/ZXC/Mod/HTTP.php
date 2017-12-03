<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 16/11/2017
 * Time: 22:41
 */

namespace ZXC\Mod;

class HTTP
{
    public $headers = [
        200 => 'OK',
        404 => 'Not Found',
        500 => 'Internal Server Error'
    ];

    public function initialize(array $config)
    {
        // TODO: Implement initialize() method.
    }

    public function sendHeader($status = 404)
    {
        header("HTTP/1.0 $status {$this->headers[$status]}");
//        header("Location: /");

    }
}