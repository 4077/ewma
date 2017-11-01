<?php namespace ewma\Modules;

use ewma\Controllers\Controller;

class Module
{
    public $namespace;

    public $path;

    public $type;

    public $masterModulePath = false;

    public $lessAutoImport;

    public $config = [];

    public $helpers = false;

    private $controller;

    public static function create($settings)
    {
        $module = new self;

        $module->namespace = $settings['namespace'];
        $module->path = $settings['path'];
        $module->type = $settings['type'];
        $module->masterModulePath = $settings['master_module_path'];
        $module->lessAutoImport = $settings['less_auto_import'];
        $module->config = $settings['config'];
        $module->helpers = $settings['helpers'];

        return $module;
    }

    public static function createFromSettingsFileData($settingsFileData)
    {
        $module = new self;

        $module->namespace = isset($settingsFileData['namespace']) ? $settingsFileData['namespace'] : '';
        $module->type = !empty($settingsFileData['type']) ? $settingsFileData['type'] : 'master';
        $module->lessAutoImport = !empty($settingsFileData['less_auto_import']) ? $settingsFileData['less_auto_import'] : '';

        return $module;
    }

    public function toCacheFormat()
    {
        return [
            'namespace'          => $this->namespace,
            'path'               => $this->path,
            'master_module_path' => $this->masterModulePath,
            'type'               => $this->type,
            'less_auto_import'   => $this->lessAutoImport,
            'config'             => $this->config,
            'helpers'            => $this->helpers
        ];
    }

    public function getController()
    {
        if (!$this->controller) {
            $controller = new Controller;

            $controller->__meta__->moduleNamespace = $this->namespace;
            $controller->__meta__->modulePath = $this->path;
            $controller->__meta__->nodePath = '-';
            $controller->__meta__->absPath = '/' . $this->path . ' -';
            $controller->__meta__->nodeId = str_replace('\\', '_', $this->namespace) . '__-';

            $controller->__meta__->module = $this;

            $this->controller = $controller;
        }

        return $this->controller;
    }

    public function getNearestMaster()
    {

    }

    public function getDir()
    {
        return abs_path('modules', $this->path);
    }
}
