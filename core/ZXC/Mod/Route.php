<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 12/11/2017
 * Time: 20:59
 */

namespace ZXC\Mod;


class Route
{
    private $type;
    private $route;
    private $reg;
    private $class;
    private $method;
    private $func;
    private $params;
    private $logger;
    private $logLevel;

    public function __construct(array $params = [])
    {
        $zxc = ZXC::getInstance();
        if (!$params) {
            $zxc->logger->critical('Route is not valid! Must be like this \'POST|/test/:route/|Class:method\'',
                $params);
            throw new \Exception(
                'Route is not valid! Must be like this \'POST|/test/:route/|Class:method\''
            );
        }
        $zxc->sysLog(__FUNCTION__ . " in " . __FILE__ . " at " . __LINE__, $params);
        foreach ($params as $item => $val) {
            $this->$item = $val;
        }
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @return mixed
     */
    public function getReg()
    {
        return $this->reg;
    }

    /**
     * @return mixed
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return mixed
     */
    public function getFunc()
    {
        return $this->func;
    }

    /**
     * @return mixed
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param mixed $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    public function executeRoute($zxc)
    {
        if ($this->class) {
            if (!class_exists($this->class)) {
                $zxc = ZXC::getInstance();
                $zxc->sysLog($this->class . ' is not defined. Can not execute route with params',
                    $this->params ? $this->params : []);
//                throw new \Exception($this->class . ' is not defined');
            }
            if (is_subclass_of($this->class, 'ZXC\Factory', true)) {
                $userClass = call_user_func(
                    $this->class . '::getInstance'
                );
                call_user_func_array(
                    [$userClass, $this->method],
                    [$zxc, $this->params]
                );
            } else {
                if (class_exists($this->class)) {
                    $userClass = new $this->class;
                    if (method_exists($userClass, $this->method)) {
                        call_user_func_array(
                            [$userClass, $this->method],
                            [$zxc, $this->params]
                        );
                    }
                }
            }
        } else {
            call_user_func_array(
                $this->func, [$zxc, $this->params]
            );
        }
    }
}