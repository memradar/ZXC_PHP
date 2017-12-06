<?php

namespace ZXC;

require_once 'Mod/Autoload.php';

use ZXC\Mod\HTTP;
use ZXC\Mod\Logger;
use ZXC\Mod\Autoload;
use ZXC\Mod\Route;
use ZXC\Traits\Config;
use ZXC\Traits\Helper;


class ZXC extends Factory
{
    private $logger;
    private $router;
    private $web = [];
    private $http;
    private $version = '0.0.1-a';

    use Helper;
    use Config;

    protected function __construct()
    {
        $this->fillMain();
    }

    public function reinitialize()
    {
        // TODO: Implement initialize() method.
    }

    private function fillMain()
    {
        $this->web['URI'] = &$_SERVER['REQUEST_URI'];
        $this->web['HOST'] = isset($_SERVER['SERVER_NAME'])
            ? $_SERVER['SERVER_NAME'] : null;
        $this->web['METHOD'] = &$_SERVER['REQUEST_METHOD'];
        $this->web['BASE_ROUTE'] = dirname($_SERVER['SCRIPT_NAME']);
        $this->web['POST'] = &$_POST;
        $this->web['GET'] = &$_GET;
    }

    public function get($key)
    {
        return isset($this->web[$key]) ? $this->web[$key] : false;
    }

    public function go()
    {
        /**
         * @var $http Mod\HTTP
         * @var $router Mod\Router
         * @var $routeParams Mod\Route
         */
        $router = $this->getModule('Router');
        $http = $this->getModule('HTTP');
        $routeParams = $router->getCurrentRoutParams(
            $this->web['URI'], $this->web['BASE_ROUTE'], $this->web['METHOD']
        );
        if (!$routeParams) {
            $http->sendHeader(404);
            return false;
        }
        ob_start();
        $routeParams->executeRoute($this);
        $body = ob_get_clean();
        echo $body;
        return true;
    }

    public function sysLog($msg = '', $param = [])
    {
        /**
         * @var $logger Mod\Logger
         */
        $logger = $this->getModule('Logger');
        if (!$logger) {
            return false;
        }
        if ($logger->getLevel() !== 'debug') {
            return false;
        }
        $logger->debug($msg, $param);
        return true;
    }
}