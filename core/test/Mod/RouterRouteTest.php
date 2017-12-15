<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 14/12/2017
 * Time: 23:13
 */
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class RouterRouteTest extends TestCase
{
    /**
     * @var \ZXC\ZXC
     */
    private $zxc;
    /**
     * @var \ZXC\Mod\Route
     */
    private $route;

    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->getZXC();
    }

    /**
     * Set test class variables $config and $zxc
     */
    public function getZXC()
    {
        $config = $this->config = require_once '../config/config.php';
        $this->zxc = require_once '../../ZXC/../index.php';
    }

    public function testRouterInstanceWithoutParameters()
    {
        $this->expectException(InvalidArgumentException::class);
        new \ZXC\Mod\Route();
    }

    public function testRouterInstanceWithParameters()
    {
        $this->route = new \ZXC\Mod\Route();
    }

    public function testCreateZXC()
    {
        $this->assertEquals('user@example.com', 'user@example.com1');
    }
}