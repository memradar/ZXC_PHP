<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 06/11/2017
 * Time: 00:40
 */
declare( strict_types=1 );

use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    public function __construct( $name = null, array $data = [], $dataName = '' )
    {
        $this->zxc = require_once '../../index.php';
        $this->router = \ZXC\Router::getInstance();
        parent::__construct( $name, $data, $dataName );
    }

    public function testDisableRouterType()
    {

    }

    public function testRegisterRoutes()
    {

    }
}