<?php

namespace ZXC;

require_once 'ZXCModules/Autoload.php';

use ZXC\Traits\Singleton;
use ZXC\ZXCModules\HTTP;
use ZXC\ZXCModules\Config;
use ZXC\ZXCModules\Logger;
use ZXC\ZXCModules\Router;

class ZXC /*extends Factory*/
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
        Config::getInstance($config);

        $this->http = HTTP::getInstance();

        $loggerConfig = Config::get('ZXC\Mod/Logger');
        if ($loggerConfig) {
            $this->logger = new Logger($loggerConfig);
        }

        $routerParams = Config::get('ZXC\Mod/Router');
        if ($routerParams) {
            $this->router = Router::getInstance($routerParams);
        } else {
            throw new \InvalidArgumentException();
        }
    }

    public function go()
    {
        /**
         * @var $http Mod\HTTP
         * @var $router Mod\Router
         * @var $routeParams Mod\Route
         */
//        $router = $this->getModule('Router');
//        $http = $this->getModule('HTTP');
        $routeParams = $this->router->getCurrentRoutParams(
            $this->http->getPath(), $this->http->getBaseRoute(), $this->http->getMethod()
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