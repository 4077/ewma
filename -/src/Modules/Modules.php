<?php namespace ewma\Modules;

use ewma\App\App;
use ewma\Service\Service;
use ewma\Autoload;

class Modules extends Service
{
    protected $services = ['app'];

    /**
     * @var App
     */
    public $app = App::class;

    //
    //
    //

    private $modulesByPath = [];

    private $modulesByNamespace = [];

    protected function boot()
    {
        if (null !== $modulesCache = $this->readFromCache()) {
            foreach ($modulesCache as $moduleCacheData) {
                $module = Module::create($moduleCacheData);

                $this->modulesByPath[$module->path] = $module;
                $this->modulesByNamespace[$module->namespace] = $module;

                if ($module->path && $module->helpers) {
                    require abs_path($module->getDir(), '/-/src/helpers.php');
                }
            }
        } else {
            $this->registerModules();
            $this->saveToCache();
        }

        foreach ($this->modulesByPath as $module) {
            Autoload::registerModule($module->namespace, $module->path);
        }
    }

    private function readFromCache()
    {
        return $this->app->cache->read('modules');
    }

    private function saveToCache()
    {
        $modulesCache = [];

        foreach ($this->modulesByPath as $module) {
            /* @var $module Module */
            $modulesCache[] = $module->toCacheFormat();
        }

        $this->app->cache->write('modules', $modulesCache);
    }

    private function registerModules()
    {
        $this->registerRecursion();
    }

    private function registerRecursion($modulePathArray = [], $masterModulePath = '')
    {
        $modulePath = a2p($modulePathArray);
        $moduleDir = abs_path('modules', $modulePath);

        if (file_exists($moduleDir . '/settings.php')) {
            $moduleSettingsFileData = require $moduleDir . '/settings.php';
        } else {
            throw new \Exception('Settings file for module "' . $modulePath . '" does not exists');
        }

        $module = Module::createFromSettingsFileData($moduleSettingsFileData);

        $module->path = $modulePath;

        if ($module->type == 'slave') {
            $module->masterModulePath = $masterModulePath;
        } else {
            $module->masterModulePath = a2p($modulePathArray);
        }

        $module->config = $this->app->configs->load($module);

        if (file_exists($moduleDir . '/-/src/helpers.php')) {
            $module->helpers = true;
        }

        $this->modulesByPath[$module->path] = $module;
        $this->modulesByNamespace[$module->namespace] = $module;

        foreach (new \DirectoryIterator($moduleDir) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }

            if ($fileInfo->isDir()) {
                $fileName = $fileInfo->getFilename();
                if ($fileName != '-') {
                    $modulePathArray[] = $fileName;

                    $this->registerRecursion($modulePathArray, isset($module) ? $module->masterModulePath : '');

                    array_pop($modulePathArray);
                }
            }
        }
    }

    /**
     * @return Module
     */
    public function getRootModule()
    {
        if (isset($this->modulesByPath[''])) {
            return $this->modulesByPath[''];
        }
    }

    /**
     * @param $path
     *
     * @return Module
     */
    public function getByPath($path = '')
    {
        if (isset($this->modulesByPath[$path])) {
            return $this->modulesByPath[$path];
        }
    }

    /**
     * @param $namespace
     *
     * @return Module
     */
    public function getByNamespace($namespace)
    {
        if (isset($this->modulesByNamespace[$namespace])) {
            return $this->modulesByNamespace[$namespace];
        }
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->modulesByPath;
    }

    /**
     * @param $modulePath
     *
     * @return string
     */
    public function getDir($modulePath)
    {
        return abs_path('modules', $modulePath);
    }
}
