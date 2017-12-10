<?php

namespace ZXC;

require_once 'Mod/Autoload.php';

use ZXC\Mod\HTTP;
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

    protected function __construct(array $config = [])
    {
        if ($config) {
            $this->setConfig($config);
        }
    }

    function reinitialize()
    {
        // TODO: Implement reinitialize() method.
    }

    /**
     * @deprecated
     */
    private function initializeMainProperties()
    {
        $this->http = $this->getModule('HTTP');
    }

    public function get($key)
    {
        return isset($this->$key) ? $this->$key : null;
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
            $http->getPath(), $http->getBaseRoute(), $http->getMethod()
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