<?php

namespace ZXC;

require_once 'ZXCModules/Autoload.php';

use ZXC\Traits\Singleton;
use ZXC\ZXCModules\HTTP;
use ZXC\ZXCModules\Config;
use ZXC\ZXCModules\Logger;
use ZXC\ZXCModules\Router;

class ZXC
{
    use Singleton;
    private $version = '0.0.1-a';
    /**
     * @var HTTP
     */
    private $http;
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var Router
     */
    private $router;

    function initialize(array $config = [])
    {
        Config::getInstance()->initialize($config);

        $this->http = HTTP::getInstance();

        $loggerConfig = Config::get('ZXC\Mod/Logger');
        if ($loggerConfig) {
            $this->logger = new Logger($loggerConfig);
        }

        $routerParams = Config::get('ZXC\Mod/Router');
        if ($routerParams) {
            $this->router = Router::getInstance();
            $this->router->initialize($routerParams);
        } else {
            throw new \InvalidArgumentException();
        }
    }

    public function go()
    {
        /**
         * @var $routeParams ZXCModules\Route
         */
        $routeParams = $this->router->getCurrentRoutParams(
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

    public function writeLog($msg = '', $param = []): bool
    {
        if ($this->logger->getLevel() !== 'debug') {
            return false;
        }
        $this->logger->debug($msg, $param);
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