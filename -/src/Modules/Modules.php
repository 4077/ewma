<?php namespace ewma\Modules;

use ewma\App\App;
use ewma\Service\Service;
use ewma\Autoload;

class Modules extends Service
{
    protected $services = ['app', 'dev'];

    /**
     * @var App
     */
    public $app = App::class;

    /**
     * @var \ewma\Modules\Dev
     */
    public $dev = \ewma\Modules\Dev::class;

    //
    //
    //

    private $cache;

    private $cacheByPath;

    private $cacheByNamespace;

    public function getCache()
    {
        if (null === $this->cache) {
            $this->cache = $this->app->cache->read('modules');
        }

        return $this->cache;
    }

    protected function boot()
    {
        $modulesCache = $this->getCache();

        if (null !== $modulesCache) {
            foreach ($modulesCache as $moduleCacheData) {
                $path = $moduleCacheData['path'];
                $namespace = $moduleCacheData['namespace'];
                $dir = $moduleCacheData['dir'];
                $helpers = $moduleCacheData['helpers'];

                $this->cacheByPath[$path] = $moduleCacheData;
                $this->cacheByNamespace[$namespace] = $moduleCacheData;

                Autoload::registerModule($namespace, $path, $dir);

                if ($path && $helpers) {
                    require_once abs_path($dir, '/-/src/helpers.php');
                }
            }
        } else {
            $this->registerModules();
            $this->saveToCache();

            $this->app->events->bind('app/terminate', function () {
                $this->saveToDb();
            });
        }

        $this->load();
    }

    /**
     * @var array Module[]
     */
    private $modulesByPath = [];

    /**
     * @var array Module[]
     */
    private $modulesByNamespace = [];

    public function reload()
    {
        $this->cacheByPath = [];
        $this->cacheByNamespace = [];

        $this->modulesByPath = [];
        $this->modulesByNamespace = [];

        $this->registerModules();
        $this->saveToCache();

        $this->load();
    }

    private function load()
    {
        foreach ($this->modulesByPath as $module) {
            Autoload::registerModule($module->namespace, $module->path, $module->dir);

            if ($module->path && $module->helpers) {
                require_once abs_path($module->dir, '/-/src/helpers.php');
            }
        }
    }

    private function registerModules()
    {
        $this->registerLocalModulesRecursion();
        $this->registerVendorModules();
//        $this->registerExternalModules();

        $this->registerVirtualModules();
    }

    private function saveToCache()
    {
        $modulesCache = [];

        foreach ($this->modulesByPath as $module) {
            $modulesCache[$module->id] = $module->toCacheFormat();
        }

        $this->app->cache->write('modules', $modulesCache);
    }

    private function saveToDb()
    {
        foreach ($this->modulesByPath as $module) {
            $model = \ewma\models\Module::where('namespace', $module->namespace)->first();

            if (!$model) {
                \ewma\models\Module::create([
                                                'namespace' => $module->namespace
                                            ]);
            }
        }
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

    private function registerLocalModulesRecursion($modulePathArray = [], $masterModulePath = '', $parentId = 0)
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

                            $this->registerLocalModulesRecursion($modulePathArray, $module->masterModulePath ?? '', $module->id);

                            array_pop($modulePathArray);
                        }
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

    private function registerVendorModules()
    {
        $vendorDir = abs_path('modules-vendor');

        foreach (new \DirectoryIterator($vendorDir) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }

            if ($fileInfo->isDir()) {
                $vendor = $fileInfo->getFilename();

                $this->registerVendorModulesRecursion($vendor);
            }
        }
    }

    private function registerVendorModulesRecursion($vendor, $modulePathArray = [], $masterModulePath = '', $parentId = 0)
    {
        $modulePath = a2p($modulePathArray);
        $isRootLevel = !$modulePathArray;

        $moduleDir = abs_path('modules-vendor', $vendor, $modulePath);

        $settingsFilePath = $moduleDir . '/settings.php';

        if (file_exists($settingsFilePath)) {
            $settings = require $settingsFilePath;

            $hasLocated = isset($this->modulesByNamespace[$settings['namespace']]);

            if (!$hasLocated) {
                ra($settings, [
                    'location'           => 'vendor',
                    'vendor'             => $vendor,
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

                            $this->registerVendorModulesRecursion($vendor, $modulePathArray, $module->masterModulePath ?? '', $module->id ?? 1);

                            array_pop($modulePathArray);
                        }
                    }
                }
            }
        } else {
            foreach (new \DirectoryIterator($moduleDir) as $fileInfo) {
                if ($fileInfo->isDot()) {
                    continue;
                }

                if ($fileInfo->isDir()) {
                    $fileName = $fileInfo->getFilename();

                    if ($fileName != '-') {
                        $modulePathArray[] = $fileName;

                        $this->registerVendorModulesRecursion($vendor, $modulePathArray, $module->masterModulePath ?? '', $module->id ?? 1);

                        array_pop($modulePathArray);
                    }
                }
            }
        }
    }

    private function registerExternalModules()
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

    private function registerVirtualModules()
    {
        $namespaces = array_keys($this->modulesByNamespace);

        $notExistsNamespaces = [];

        foreach ($namespaces as $namespace) {
            $namespaceArray = explode('\\', $namespace);

            $parentNamespace = implode('\\', array_slice($namespaceArray, 0, -1));

            if (!isset($this->modulesByNamespace[$parentNamespace])) {
                $notExistsNamespaces[] = $parentNamespace;
            }
        }

        $notExistsNamespaces = merge($notExistsNamespaces);

        foreach ($notExistsNamespaces as $notExistsNamespace) {
            $modulePath = str_replace('\\', '/', $notExistsNamespace);
            $moduleDir = abs_path('modules/virtualModules', $modulePath);

            mdir($moduleDir);

            $settings = [
                'location'           => 'local',
                'virtual'            => true,
                'id'                 => ++$this->currentModuleId,
                'parent_id'          => 0,
                'path'               => $modulePath,
                'dir'                => $moduleDir,
                'master_module_path' => $modulePath,
                'namespace'          => $notExistsNamespace
            ];

            $this->registerModule($settings);
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
        return $this->getByPath();
    }

    /**
     * @param $path
     *
     * @return Module
     */
    public function getByPath($path = '')
    {
        if (!isset($this->modulesByPath[$path])) {
            if ($moduleCacheData = $this->cacheByPath[$path] ?? false) {
                $module = Module::create($moduleCacheData);

                $this->modulesByPath[$module->path] = $module;
                $this->modulesByNamespace[$module->namespace] = $module;
            } else {
                $this->modulesByPath[$path] = false;
            }
        }

        return $this->modulesByPath[$path];
    }

    /**
     * @param $namespace
     *
     * @return Module
     */
    public function getByNamespace($namespace)
    {
        if (!isset($this->modulesByNamespace[$namespace])) {
            if ($moduleCacheData = $this->cacheByNamespace[$namespace] ?? false) {
                $module = Module::create($moduleCacheData);

                $this->modulesByNamespace[$module->path] = $module;
                $this->modulesByNamespace[$module->namespace] = $module;
            } else {
                $this->modulesByNamespace[$namespace] = false;
            }
        }

        return $this->modulesByNamespace[$namespace];
    }

    /**
     * @return Module[]
     */
    public function getAll()
    {
        foreach ($this->cacheByPath as $path => $moduleCacheData) {
            $this->getByPath($path);
        }

        return $this->modulesByPath;
    }
}
