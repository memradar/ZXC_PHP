<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 17/11/2017
 * Time: 00:04
 */

namespace ZXC\Mod;


use ZXC\Factory;
use ZXC\Traits\GetSet;
use ZXC\ZXC;

class Session extends Factory
{
    use GetSet;
    protected $lifeTime;
    private $sess;
    /**
     * @var $prefix
     * $_SESSION[$this->>prefix]
     */
    private $prefix;
    private $path;
    private $domain;
    private $name;

    /**
     * Session constructor.
     * @param array $config ['time'=>7200]
     */
    public function __construct(array $config = [])
    {
        $this->init($config);
    }

    /**
     * Initialize session
     * @param array $config
     */
    public function init(array $config = [])
    {
        $zxc = ZXC::getInstance();
        if (!isset($config['time'])) {
            $this->lifeTime = 7200;
        } else {
            $this->lifeTime = $config['time'];
        }
        if (!isset($config['path'])) {
            $this->path = '/';
        } else {
            $this->path = $config['path'];
        }
        if (!isset($config['domain'])) {
            /**
             * @var $http HTTP
             */
            $http = $zxc->getModule('HTTP');
            $this->domain = $http ? $http->getHost() : $http;
        } else {
            $this->domain = $config['domain'];
        }
        if (!isset($config['name'])) {
            //if session.auto_start is enabled by default you must set session name in php.ini
            $this->name = 'zxc';
        } else {
            $this->name = $config['name'];
        }
        if (isset($config['prefix']) && is_string($config['prefix'])) {
            $this->prefix = $config['prefix'];
        } else {
            $this->prefix = 'zxc';
        }

        session_name($this->name);
        session_set_cookie_params($this->lifeTime, $this->path, $this->domain);
        $this->start();
        $this->sess = &$_SESSION;
        if (!isset($this->sess[$this->prefix])) {
            $this->sess[$this->prefix] = [];
            $this->set('id', session_id());
            $this->set('start', $this->getTime());
        }
    }

    /**
     * Set new parameter in zxc session
     * @param $key - parameter name
     * @param $val - parameter value
     * @return bool
     */
    public function set($key, $val)
    {
        if (empty($key) || empty($val)) {
            return false;
        }
        return $this->sess[$this->prefix][$key] = $val;
    }

    /**
     * Get parameter from zxc session
     * @param $key - parameter name
     * @return bool
     */
    public function get($key)
    {
        if (isset($this->sess)) {
            return $this->sess[$this->prefix][$key];
        }
        return false;
    }

    public function delete($key)
    {
        if (isset($this->sess)) {
            unset($this->sess[$this->prefix][$key]);
            return true;
        }
        return false;
    }

    /**
     * Start session
     */
    public function start()
    {
        if (!isset($_SESSION)) {
            session_start();
        }
    }

    /**
     * Clear zxc session
     */
    public function clear()
    {
        $this->sess[$this->prefix] = [];
    }

    /**
     * Check session is enabled
     * @return bool
     */
    public function isEnabled()
    {
        if (($this->getTime() - $this->get('start')) > $this->lifeTime) {
            $this->clear();
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get current Unix timestamp
     * @return int
     */
    public function getTime()
    {
        return time();
    }

    public function destroy()
    {
        if (isset($_SESSION)) {
            unset($_SESSION[$this->prefix]);
            return true;
        }
        return false;
    }

    function reinitialize()
    {
        // TODO: Implement reinitialize() method.
        $this->init();
    }
}