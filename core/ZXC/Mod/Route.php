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

    private function callBefore(ZXC $zxc, $mainClass = null)
    {
        $paramsForSecondRouteArguments['routeParams'] = $this->params;
        $resultBefore = null;
        if ($this->before) {
            if (is_array($this->before)) {
                if (class_exists($this->before['class'])) {
                    if ($mainClass && get_class($mainClass) === $this->before['class']) {
                        $userClassBefore = $mainClass;
                    } else {
                        $userClassBefore = new $this->before['class'];
                    }
                    if ($this->hooksResultTransfer) {
                        $resultBefore = call_user_func_array(
                            [$userClassBefore, $this->before['method']],
                            [$zxc, $paramsForSecondRouteArguments]
                        );
                    } else {
                        call_user_func_array(
                            [$userClassBefore, $this->before['method']],
                            [$zxc, $paramsForSecondRouteArguments]
                        );
                    }
                }
            } else {
                if ($this->hooksResultTransfer) {
                    $resultBefore = call_user_func_array(
                        $this->before, [$zxc, $paramsForSecondRouteArguments]
                    );
                } else {
                    call_user_func_array(
                        $this->before, [$zxc, $paramsForSecondRouteArguments]
                    );
                }
            }
        }
        return $resultBefore;
    }

    private function callAfter(ZXC $zxc, $resultMain = null, $mainClass = null)
    {
        $paramsForSecondRouteArguments['routeParams'] = $this->params;
        $paramsForSecondRouteArguments['resultMain'] = $resultMain;

        if ($this->after) {
            if (is_array($this->after)) {
                if (class_exists($this->after['class'])) {

                    if ($mainClass && get_class($mainClass) === $this->after['class']) {
                        $userClassBefore = $mainClass;
                    } else {
                        $userClassBefore = new $this->after['class'];
                    }
                    call_user_func_array(
                        [$userClassBefore, $this->after['method']],
                        [$zxc, $paramsForSecondRouteArguments]
                    );
                }
            } else {
                call_user_func_array(
                    $this->after, [$zxc, $paramsForSecondRouteArguments]
                );
            }
        }
        return true;
    }

    public function executeRoute($zxc)
    {
        $resultMainFunc = null;
        $resultBefore = null;
        $resultAfter = null;
        $paramsForSecondRouteArguments = [];
        $paramsForSecondRouteArguments['routeParams'] = $this->params;
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
                    [$zxc, $paramsForSecondRouteArguments]
                );
            } else {
                if (class_exists($this->class)) {
                    $userClass = new $this->class;
                    if (is_subclass_of($this->class, 'ZXC\Interfaces\Module', true)) {
                        if (method_exists($userClass, 'initialize')) {
                            $userClass->initialize();
                        }
                    }
                    $resultBefore = $this->callBefore($zxc, $userClass);
                    if (method_exists($userClass, $this->method)) {
                        if ($this->hooksResultTransfer) {
                            $paramsForSecondRouteArguments['resultBefore'] = $resultBefore;
                            $resultMainFunc = call_user_func_array(
                                [$userClass, $this->method],
                                [$zxc, $paramsForSecondRouteArguments]
                            );
                            $this->callAfter($zxc, $resultMainFunc, $userClass);
                        } else {
                            call_user_func_array(
                                [$userClass, $this->method],
                                [$zxc, $paramsForSecondRouteArguments]
                            );
                            $this->callAfter($zxc, null, $userClass);
                        }
                    }
                }
            }
        } elseif (is_callable($this->func)) {
            //TODO check double initialize when we are using before and after hooks from same class (we are colling __construct twice)
            $resultBefore = $this->callBefore($zxc);
            if ($this->hooksResultTransfer) {
                $paramsForSecondRouteArguments['resultBefore'] = $resultBefore;
                $resultMainFunc = call_user_func_array(
                    $this->func, [$zxc, $paramsForSecondRouteArguments]
                );
                $this->callAfter($zxc, $resultMainFunc);
            } else {
                call_user_func_array(
                    $this->func, [$zxc, $paramsForSecondRouteArguments]
                );
                $this->callAfter($zxc);
            }
        } else {
            throw new \InvalidArgumentException('Main function or method is not defined for the route');
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