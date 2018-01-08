<?php

namespace ZXC;

require_once 'Mod/Autoload.php';

use PHPUnit\Runner\Exception;
use ZXC\Traits\Config;

class ZXC extends Factory
{
    private $version = '0.0.1-a';

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

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }
}