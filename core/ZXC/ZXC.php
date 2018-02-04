<?php

namespace ZXC;

require_once 'ZXCModules/Autoload.php';

use ZXC\Patterns\Singleton;
use ZXC\ZXCModules\Autoload;
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

        $configAutoloadDir = Config::get('ZXC/Autoload');
        Autoload::getInstance()->initialize($configAutoloadDir);

        $this->http = HTTP::getInstance();

        $loggerConfig = Config::get('ZXC/Logger');
        if ($loggerConfig) {
            $this->logger = new Logger($loggerConfig);
        }

        $routerParams = Config::get('ZXC/Router');
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
        //TODO add codes for Exception
        try {
            $routeHandler = $routeParams->executeRoute($this);
            $body = ob_get_clean();
        } catch (\InvalidArgumentException $e) {
            ob_end_clean();
            $body = '';
            $routeHandler = '';
        } catch (\Exception $e) {
            ob_end_clean();
            $body = '';
            $routeHandler = '';
        }

        echo json_encode(['status' => 200, 'body' => $body, 'handler' => $routeHandler]);
        return true;
    }

    public function writeLog($msg = '', $param = []): bool
    {
        if (!$this->logger) {
            return false;
        }
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

    /**
     * @return HTTP
     */
    public function getHttp(): HTTP
    {
        return $this->http;
    }

    /**
     * @return Logger
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }

    /**
     * @return Router
     */
    public function getRouter(): Router
    {
        return $this->router;
    }
}