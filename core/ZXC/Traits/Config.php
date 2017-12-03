<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 30/11/2017
 * Time: 22:05
 */

namespace ZXC\Traits;

trait Config
{
    private $configModule;

    public function setConfig(array $config = [])
    {
        if (!$config) {
            return false;
        }

        foreach ($config as $nameSpaces => $modules) {
            foreach ($modules as $moduleName => $moduleParams) {
                $class = $this->getModuleClassName($nameSpaces, $moduleName, $moduleParams);
                if ($class) {
                    $this->configModule[$moduleName] = $class;
                }
            }
        }
        foreach ($this->configModule as $module) {
            if (method_exists($module, 'initialize')) {
                $module->initialize();
            }
        }
        return true;
    }

    private function getModuleClassName($nameSpaces, $moduleName, $params)
    {
        $className = $nameSpaces . '\\' . $moduleName;
        if (class_exists($className)) {
            if (is_subclass_of($className, 'ZXC\Factory', true)) {
                return call_user_func($className . '::getInstance', $params);
            } else {
                return new $className($params);
            }
        }
        return false;
    }

    public function getModule($moduleName)
    {
        if (!$moduleName || !isset($this->configModule[$moduleName])) {
            return false;
        }
        return $this->configModule[$moduleName];
    }
}