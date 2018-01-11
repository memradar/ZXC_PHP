<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 11/01/2018
 * Time: 23:34
 */

namespace ZXC\ZXCModules;

use ZXC\Patterns\Singleton;

/**
 * Class DB
 * Main DB Singleton for ZXC
 * @package ZXC\ZXCModules
 */
class DB
{
    use Singleton;
    /**
     * @var \ZXC\Classes\DB
     */
    private $pdo;

    /**
     * DB constructor.
     * ZXC/DB must be defined in config file if you use instance of this class
     * @throws \Exception
     */
    private function __construct()
    {
        $config = Config::get('ZXC/DB');
        if (!$config) {
            throw new \Exception('ZXC/DB is not defined in config');
        }
        $this->pdo = new \ZXC\Classes\DB($config);
    }

    /**
     * A proxy to \ZXC\Classes\DB
     * @param $method
     * @param $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->pdo, $method), $args);
    }
}