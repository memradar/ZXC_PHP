<?php

namespace ZXC;

require_once 'Mod/Autoload.php';

use ZXC\Mod\Autoload;
use ZXC\Traits\Helper;


class ZXC extends Factory
{
    private $router;
    private $main = [];

    use Helper;

    protected function __construct()
    {
        $this->fillMain();
        $this->router = Router::getInstance('fds');
    }

    public function registerRoutes($routes = [])
    {
        if (!$this->router || !$routes) {
            return false;
        }
        $this->router->registerRoutes($routes);

        return true;
    }

    private function fillMain()
    {
        $this->main['AUTOLOAD'] = Autoload::getInstance();
        $this->main['AUTOLOAD']->setAutoloadDirectories(['' => true]);
        $this->main['URI'] = &$_SERVER['REQUEST_URI'];
        $this->main['HOST'] = isset($_SERVER['SERVER_NAME'])
            ? $_SERVER['SERVER_NAME'] : null;
        $this->main['METHOD'] = &$_SERVER['REQUEST_METHOD'];
        $this->main['BASE_ROUTE'] = dirname($_SERVER['SCRIPT_NAME']);
        $this->main['ROUTE'] = '';
        $this->main['POST'] = &$_POST;
        $this->main['GET'] = &$_GET;
        $this->main['LOGGER'] = new Logger\Logger();
    }

    public function set($key, $val)
    {
        if ($key === 'AUTOLOAD') {
            $this->main['AUTOLOAD']->setAutoloadDirectories($val);
        } else {
            if (isset($this->main, $key)) {
                $this->main[$key] = $val;
            }
        }
    }

    public function go()
    {
        $routeParams = $this->router->getCurrentRoutParams(
            $this->main['URI'], $this->main['BASE_ROUTE'], $this->main['METHOD']
        );
        if (!$routeParams) {
            return false;
        } //TODO 404

        ob_start();
        $routeParams->executeRoute($this);
        $body = ob_get_clean();
        echo $body;

        return true;
    }
}