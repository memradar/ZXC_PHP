<?php

namespace ZXC;

require_once 'Mod/Autoload.php';

use ZXC\Mod\HTTP;
use ZXC\Mod\Autoload;
use ZXC\Mod\Route;
use ZXC\Traits\Config;
use ZXC\Traits\Helper;


class ZXC extends Factory
{
    /**
     * @var http Mod\HTTP
     */
    private $http;
    private $version = '0.0.1-a';

    use Helper;
    use Config;

    protected function __construct()
    {
        $this->initializeMainProperties();
    }

    public function reinitialize()
    {
        $this->initializeMainProperties();
    }

    private function initializeMainProperties()
    {
        $this->http = HTTP::getInstance();
    }

    public function get($key)
    {
        return isset($this->$key) ? $this->$key : null;
    }

    public function go()
    {
        /**
         * @var $this ->http Mod\HTTP
         * @var $router Mod\Router
         * @var $routeParams Mod\Route
         */
        $router = $this->getModule('Router');
//        $http = $this->getModule('HTTP');
        $routeParams = $router->getCurrentRoutParams(
            $this->http->getPath(), $this->http->getBaseRoute(), $this->http->getMethod()
        );
        if (!$routeParams) {
            $this->http->sendHeader(404);
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