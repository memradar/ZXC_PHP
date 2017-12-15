<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 12/11/2017
 * Time: 20:59
 */

namespace ZXC\Mod;


use ZXC\ZXC;

class Route
{
    private $type;
    private $route;
    private $reg;
    private $class;
    private $method;
    private $func;
    private $params;
    private $before;
    private $after;
    private $hooksResultTransfer;
    private $children;

    public function __construct(array $params = [])
    {
        $zxc = ZXC::getInstance();
        if (!$params) {
            $logger = $zxc->getModule('Logger');
            if ($logger) {
                $logger->critical('Route is not valid! Must be like this \'POST|/test/:route/|Class:method\'',
                    $params);
            }
            throw new \InvalidArgumentException(
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

    private function callBefore(ZXC $zxc)
    {
        $resultBefore = null;
        if ($this->before) {
            if (is_array($this->before)) {
                if (class_exists($this->before['class'])) {
                    $userClassBefore = new $this->before['class'];
                    if ($this->hooksResultTransfer) {
                        $resultBefore = call_user_func_array(
                            [$userClassBefore, $this->before['method']],
                            [$zxc, $this->params]
                        );
                    } else {
                        call_user_func_array(
                            [$userClassBefore, $this->before['method']],
                            [$zxc, $this->params]
                        );
                    }
                }
            } else {
                if ($this->hooksResultTransfer) {
                    $resultBefore = call_user_func_array(
                        $this->before, [$zxc, $this->params]
                    );
                } else {
                    call_user_func_array(
                        $this->before, [$zxc, $this->params]
                    );
                }
            }
        }
        return $resultBefore;
    }

    private function callAfter(ZXC $zxc, $result = null)
    {
        if ($this->after) {
            if (is_array($this->after)) {
                if (class_exists($this->after['class'])) {
                    $userClassBefore = new $this->after['class'];
                    if ($result) {
                        call_user_func_array(
                            [$userClassBefore, $this->after['method']],
                            [$zxc, $this->params, $result]
                        );
                    } else {
                        call_user_func_array(
                            [$userClassBefore, $this->after['method']],
                            [$zxc, $this->params]
                        );
                    }
                }
            } else {
                if ($result) {
                    call_user_func_array(
                        $this->after, [$zxc, $this->params, $result, $result]
                    );
                } else {
                    call_user_func_array(
                        $this->after, [$zxc, $this->params]
                    );
                }
            }
        }
        return true;
    }

    public function executeRoute($zxc)
    {
        $resultMainFunc = null;
        $resultBefore = null;
        $resultAfter = null;

        if ($this->class) {
            if (!class_exists($this->class)) {
                $zxc = ZXC::getInstance();
                $zxc->sysLog($this->class . ' is not defined. Can not execute route with params',
                    $this->params ? $this->params : []);
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
                    if (is_subclass_of($this->class, 'ZXC\Interfaces\Module', true)) {
                        if (method_exists($userClass, 'initialize')) {
                            $userClass->initialize();
                        }
                    }
                    $resultBefore = $this->callBefore($zxc);
                    if (method_exists($userClass, $this->method)) {
                        if ($this->hooksResultTransfer) {
                            $resultMainFunc = call_user_func_array(
                                [$userClass, $this->method],
                                [$zxc, $this->params, $resultBefore]
                            );
                            $this->callAfter($zxc, $resultMainFunc);
                        } else {
                            call_user_func_array(
                                [$userClass, $this->method],
                                [$zxc, $this->params]
                            );
                            $this->callAfter($zxc);
                        }
                    }
                }
            }
        } else {
            call_user_func_array(
                $this->func, [$zxc, $this->params]
            );
        }
    }

    /**
     * @return mixed
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return mixed
     */
    public function getBefore()
    {
        return $this->before;
    }

    /**
     * @return mixed
     */
    public function getAfter()
    {
        return $this->after;
    }

    /**
     * @return mixed
     */
    public function getHooksResultTransfer()
    {
        return $this->hooksResultTransfer;
    }
}