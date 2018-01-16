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

    private $cache;

    public function getCache()
    {
        if (null === $this->cache) {
            $this->cache = $this->app->cache->read('modules');
        }

        return $this->cache;
    }

    protected function boot()
    {
        if (null !== $modulesCache = $this->getCache()) {
            foreach ($modulesCache as $moduleCacheData) {
                $module = Module::create($moduleCacheData);

                $this->modulesByPath[$module->path] = $module;
                $this->modulesByNamespace[$module->namespace] = $module;

                if ($module->path && $module->helpers) {
                    require abs_path($module->dir, '/-/src/helpers.php');
                }
            }
        } else {
            $this->registerModules();
            $this->saveToCache();
        }

        foreach ($this->modulesByPath as $module) {
            /* @var $module Module */
            Autoload::registerModule($module->namespace, $module->path, $module->dir);
        }
    }

    private function registerModules()
    {
        $this->localModulesRegisterRecursion();
        $this->vendorModulesRegisterRecursion();
        $this->externalModulesRegister();
    }

    private function saveToCache()
    {
        $modulesCache = [];

        foreach ($this->modulesByPath as $module) {
            /* @var $module Module */
            $modulesCache[$module->id] = $module->toCacheFormat();
        }

        $this->app->cache->write('modules', $modulesCache);
    }

    /*
     * 1. Запускается рекурсия регистрации по папке modules
     *      для каждого модуля в списке проверяется наличие файла location.php
     *          если файла нет, то регистрируется этот модуль
     *          если файл есть, то:
     *              если type=local, то регистрируется этот модуль,
     *              если type=external, то запускается рекурсия регистрации на пути external_path // todo del
     *              если type=vendor, то ничего не происходит
     *
     *      неймспейс зарегистрированного модуля добавляется в список зарегистрированных модулей
     * 2. Запускается рекурсия регистрации по папке modules-vendor
     *      если неймспейс модуля не состоит в списке зарегистрированных, то модуль регистрируется
     */

    private $currentModuleId = 0;

    private function localModulesRegisterRecursion($modulePathArray = [], $masterModulePath = '', $parentId = 0)
    {
        $modulePath = a2p($modulePathArray);

        $moduleDir = abs_path('modules', $modulePath);

        $location = 'local';

        if ($modulePathArray) {
            $locationFilePath = abs_path($moduleDir . '/location.php');

            if (file_exists($locationFilePath)) {
                $locationSettings = require $locationFilePath;

                $location = $locationSettings['type'];
            }
        }

        if ($location == 'local') {
            $settingsFilePath = $moduleDir . '/settings.php';
            if (file_exists($settingsFilePath)) {
                $settings = require $settingsFilePath;
            } else {
                $settings = [
                    'namespace' => implode('\\', $modulePathArray)
                ];
            }

            aa($settings, ['namespace' => '']);

            ra($settings, [
                'location'           => $location,
                'id'                 => ++$this->currentModuleId,
                'parent_id'          => $parentId,
                'path'               => $modulePath,
                'dir'                => $moduleDir,
                'master_module_path' => ($settings['type'] ?? 'master') == 'master'
                    ? a2p($modulePathArray)
                    : $masterModulePath
            ]);

            $module = $this->registerModule($settings);

            foreach (new \DirectoryIterator($moduleDir) as $fileInfo) {
                if ($fileInfo->isDot()) {
                    continue;
                }

                if ($fileInfo->isDir()) {
                    $fileName = $fileInfo->getFilename();

                    if ($fileName != '-') {
                        $modulePathArray[] = $fileName;

                        $this->localModulesRegisterRecursion($modulePathArray, $module->masterModulePath ?? '', $module->id);

                        array_pop($modulePathArray);
                    }
                }
            }
        }

        if ($location == 'external') {
            if (isset($locationSettings['external_path'])) {
                $basePath = $locationSettings['external_path'];

                $this->externalModuleRegisterRecursion($basePath, $modulePathArray = [], $masterModulePath = '');
            }
        }
    }

    private function vendorModulesRegisterRecursion($modulePathArray = [], $masterModulePath = '', $parentId = 0)
    {
        $modulePath = a2p($modulePathArray);

        $moduleDir = abs_path('modules-vendor', $modulePath);

        $settingsFilePath = $moduleDir . '/settings.php';
        if (file_exists($settingsFilePath)) {
            $settings = require $settingsFilePath;
        } else {
            $settings = [
                'namespace' => implode('\\', $modulePathArray)
            ];
        }

        $hasLocated = isset($this->modulesByNamespace[$settings['namespace']]);
        $isRootLevel = !$modulePathArray;

        if (!$hasLocated) {
            ra($settings, [
                'location'           => 'vendor',
                'id'                 => ++$this->currentModuleId,
                'parent_id'          => $parentId,
                'path'               => $modulePath,
                'dir'                => $moduleDir,
                'master_module_path' => ($settings['type'] ?? 'master') == 'master'
                    ? a2p($modulePathArray)
                    : $masterModulePath
            ]);

            $module = $this->registerModule($settings);
        }

        if (!$hasLocated || $isRootLevel) {
            foreach (new \DirectoryIterator($moduleDir) as $fileInfo) {
                if ($fileInfo->isDot()) {
                    continue;
                }

                if ($fileInfo->isDir()) {
                    $fileName = $fileInfo->getFilename();

                    if ($fileName != '-') {
                        $modulePathArray[] = $fileName;

                        $this->vendorModulesRegisterRecursion($modulePathArray, $module->masterModulePath ?? '', $module->id ?? 1);

                        array_pop($modulePathArray);
                    }
                }
            }
        }
    }

    private function externalModulesRegister()
    {
        $rootModule = $this->getRootModule();

        $externalModules = ap($rootModule->config, 'external_modules');

        if ($externalModules) {
            foreach ($externalModules as $localPath => $externalModulePath) {
                if (!isset($this->modulesByPath[$localPath])) {
                    $this->externalModuleRegisterRecursion($localPath, $externalModulePath);
                }
            }
        }
    }

    private function externalModuleRegisterRecursion($localPath, $externalPath, $modulePathArray = [], $masterModulePath = '', $parentId = 0)
    {
        $modulePath = a2p($modulePathArray);

        $moduleDir = $externalPath . ($modulePath ? '/' . $modulePath : '');

        $settingsFilePath = $moduleDir . '/settings.php';
        if (file_exists($settingsFilePath)) {
            $settings = require $settingsFilePath;
        } else {
            $settings = [
                'namespace' => implode('\\', $modulePathArray)
            ];
        }

        $hasLocated = isset($this->modulesByNamespace[$settings['namespace']]);
        $isRootLevel = !$modulePathArray;

        if (!$hasLocated) {
            ra($settings, [
                'location'           => 'external',
                'id'                 => ++$this->currentModuleId,
                'parent_id'          => $parentId,
                'path'               => path($localPath, $modulePath),
                'dir'                => $moduleDir,
                'master_module_path' => ($settings['type'] ?? 'master') == 'master'
                    ? a2p($modulePathArray)
                    : $masterModulePath
            ]);

            $module = $this->registerModule($settings);
        }

        if (!$hasLocated || $isRootLevel) {
            foreach (new \DirectoryIterator($moduleDir) as $fileInfo) {
                if ($fileInfo->isDot()) {
                    continue;
                }

                if ($fileInfo->isDir()) {
                    $fileName = $fileInfo->getFilename();

                    if ($fileName != '-') {
                        $modulePathArray[] = $fileName;

                        $this->externalModuleRegisterRecursion($localPath, $externalPath, $modulePathArray, $module->masterModulePath ?? '', $module->id ?? 1);

                        array_pop($modulePathArray);
                    }
                }
            }
        }
    }

    /**
     * @return Module
     */
    private function registerModule($settings)
    {
        $module = Module::create($settings);

        $module->config = $this->app->configs->load($module);

        if (file_exists($module->dir . '/-/src/helpers.php')) {
            $module->helpers = true;
        }

        $this->modulesByPath[$module->path] = $module;
        $this->modulesByNamespace[$module->namespace] = $module;

        return $module;
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
}
