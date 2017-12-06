<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 17/11/2017
 * Time: 00:04
 */

namespace ZXC\Mod;


use ZXC\Interfaces\Module;
use ZXC\ZXC;

class Session implements Module
{
    protected $lifeTime;
    private $sess;
    private $prefix;
    private $path;
    private $domain;

    /**
     * Session constructor.
     * @param array $config ['time'=>7200]
     */
    public function __construct(array $config = [])
    {
        $zxc = ZXC::getInstance();
        if (!isset($config['time'])) {
            $this->lifeTime = 7200;
        }
        if (!isset($config['path'])) {
            $this->path = '/';
        }
        if (!isset($config['domain'])) {
            $this->domain = $zxc->get('HOST');
        }
        $this->initialize();
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

    /**
     * Initialize session
     */
    public function initialize()
    {
        $this->start();
        session_set_cookie_params($this->lifeTime, $this->path, $this->domain);
        $this->prefix = 'zxc';
        $this->sess = &$_SESSION;
        if (!isset($this->sess[$this->prefix])) {
            $this->sess = $this->sess[$this->prefix] = [];
            $this->set('id', session_id());
            $this->set('start', $this->getTime());
        }

        $this->isEnabled();
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
}