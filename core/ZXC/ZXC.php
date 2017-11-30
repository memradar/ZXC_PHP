<?php

namespace ZXC;

require_once 'Mod/Autoload.php';

use ZXC\Mod\HTTP;
use ZXC\Mod\Logger;
use ZXC\Mod\Autoload;
use ZXC\Traits\Config;
use ZXC\Traits\Helper;


class ZXC extends Factory
{
    private $logger;
    private $router;
    private $autoload;
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
//        $this->autoload = Autoload::getInstance();
//        $this->autoload->setAutoloadDirectories();


//        $this->http = HTTP::getInstance();
//        $this->router = Router::getInstance();
//        $this->logger = new Logger();


    }

//    public function setConfig(array $config = [])
//    {
//        if (!$config) {
//            return false;
//        }
//        Config::getInstance($config, $this);
//        foreach ($config as $k => $v) {
//            $className = strtolower($k);
//            if (class_exists($className)) {
//                if (method_exists($this->$className, 'initialize')) {
//                    $this->$className->initialize($v);
//                }
//            }
//        }
//        return true;
//    }

    public function go()
    {
        $routeParams = $this->router->getCurrentRoutParams(
            $this->web['URI'], $this->web['BASE_ROUTE'], $this->web['METHOD']
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

    /**
     * @return mixed
     */
    public function getLogger()
    {
        return $this->logger;
    }

    public function sysLog($msg = '', $param = [])
    {
        if ($this->logger->getLevel() !== 'debug') {
            return false;
        }
        $this->logger->debug($msg, $param);
    }
}